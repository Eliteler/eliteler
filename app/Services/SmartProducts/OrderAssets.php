<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Services\SmartProducts;

use App\Helpers\StorageHelper;
use App\Mail\SmartProductAssetsUploaded;
use App\NfcCardDesign;
use App\NfcCardOrder;
use App\NfcCardOrderAsset;
use App\Support\SmartProducts\OrderHistory;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Image assets of Smart Products: the files a product asks for
 * (nfc_card_designs.image_assets, switched on by allow_logo_upload) and the
 * files customers uploaded for an order (nfc_card_order_assets).
 *
 * An order uses the list copied into order_details.image_assets when it was
 * placed; older orders use the product's current list. An order's old
 * single logo (nfc_card_orders.nfc_card_logo) counts as the "logo" asset.
 */
class OrderAssets
{
    public const MAX_ASSETS = 10;

    /** Order statuses in which customers may upload or replace files. */
    public const UPLOAD_STATUSES = ['processing', 'hold'];

    /** File types customers may upload (checked from the file's content). */
    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'pdf'];

    /** Where uploads are stored on the public disk. */
    private const FOLDER = 'nfc-card-assets';

    /** Old single-logo folders (Blade upload): files there may be removed when replaced. */
    private const OWN_FOLDERS = ['storage/nfc-card-assets/', 'storage/nfc-cards/'];

    /** The order's owner may upload while the order can still be printed. */
    public static function canUpload(NfcCardOrder $order, ?User $user): bool
    {
        return $user
            && (int) $order->user_id === (int) $user->id
            && in_array($order->order_status, self::UPLOAD_STATUSES, true)
            && self::requirements($order) !== [];
    }

    /**
     * Save the customer's file for one asset (replaces an earlier one), keep
     * the old single-logo column in step, add a timeline entry and email the
     * admin once every required file is there.
     *
     * @return array{total: int, uploaded: int, required: int, missingRequired: int}
     *
     * @throws ValidationException
     */
    public static function store(NfcCardOrder $order, string $key, UploadedFile $file, User $user): array
    {
        if (! self::canUpload($order, $user)) {
            throw ValidationException::withMessages(['file' => __('Files can only be uploaded while the order is processing or on hold.')]);
        }

        $requirements = self::requirements($order);
        $asset        = collect($requirements)->firstWhere('key', $key);

        if (! $asset) {
            throw ValidationException::withMessages(['file' => __('This file is not asked for on this order.')]);
        }

        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $maxKb     = (int) env('SIZE_LIMIT', 2048);

        if (! $file->isValid() || ! in_array($extension, self::EXTENSIONS, true)) {
            throw ValidationException::withMessages(['file' => __('Upload a JPG, PNG, WebP, SVG or PDF file.')]);
        }

        if ($file->getSize() / 1024 > $maxKb) {
            throw ValidationException::withMessages(['file' => __('The file is too large (max :size MB).', ['size' => round($maxKb / 1024, 1)])]);
        }

        $before = self::summary($order);
        $path   = StorageHelper::uploadImage($file, self::FOLDER);

        if (! $path) {
            throw ValidationException::withMessages(['file' => __('The file could not be saved. Please try again.')]);
        }

        return self::save($order, $requirements, $asset, $path, $extension === 'pdf' ? 'pdf' : 'image', $file->getClientOriginalName(), (int) $file->getSize(), $user, $before);
    }

    /**
     * Use a file that is already on the site (the customer's media library,
     * React panel and old app builds) for one asset.
     *
     * @throws ValidationException
     */
    public static function storeExistingPath(NfcCardOrder $order, string $key, string $path, User $user): array
    {
        if (! self::canUpload($order, $user)) {
            throw ValidationException::withMessages(['file' => __('Files can only be uploaded while the order is processing or on hold.')]);
        }

        $requirements = self::requirements($order);
        $asset        = collect($requirements)->firstWhere('key', $key);
        $extension    = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));

        if (! $asset) {
            throw ValidationException::withMessages(['file' => __('This file is not asked for on this order.')]);
        }

        // Only files stored on this site, of an allowed type
        if (str_contains($path, '..') || ! (Str::startsWith($path, 'storage/') || Str::startsWith($path, url('/'))) || ! in_array($extension, self::EXTENSIONS, true)) {
            throw ValidationException::withMessages(['file' => __('Upload a JPG, PNG, WebP, SVG or PDF file.')]);
        }

        return self::save($order, $requirements, $asset, $path, $extension === 'pdf' ? 'pdf' : 'image', basename($path), 0, $user, self::summary($order));
    }

    /** Record a file for an asset: replace, keep the logo column in step, timeline, email. */
    private static function save(NfcCardOrder $order, array $requirements, array $asset, string $path, string $type, ?string $originalName, int $size, User $user, array $before): array
    {
        $key   = $asset['key'];
        $files = self::files($order, $requirements);

        // Replace: remove the earlier file if it's one of ours (never a media-library file)
        $old = $files[$key]['path'] ?? null;
        if ($old && $old !== $path && Str::startsWith($old, self::OWN_FOLDERS)) {
            StorageHelper::deleteFile($old);
        }

        NfcCardOrderAsset::updateOrCreate(
            ['nfc_card_order_id' => $order->nfc_card_order_id, 'asset_key' => $key],
            [
                'file_path'     => $path,
                'file_type'     => $type,
                'original_name' => $originalName ? mb_substr($originalName, 0, 191) : null,
                'size'          => $size,
                'uploaded_by'   => $user->id,
            ]
        );

        // Old screens and old app builds read the single logo column (images only)
        $logoKey = collect($requirements)->firstWhere('key', 'logo')['key'] ?? $requirements[0]['key'];
        if ($key === $logoKey && $type !== 'pdf') {
            $order->forceFill(['nfc_card_logo' => $path])->save();
        }

        OrderHistory::record($order, $order->order_status, [
            'message'    => ($old ? __('Image asset replaced: :name', ['name' => $asset['name']]) : __('Image asset uploaded: :name', ['name' => $asset['name']])),
            'created_by' => $user->id,
        ]);

        $after = self::summary($order->fresh());

        // Email the admin when the order has every required file (and on later changes)
        if ($after['missingRequired'] === 0) {
            self::notify($order->fresh(), $user, $asset['name'], (bool) $old, $before['missingRequired'] > 0);
        }

        return $after;
    }

    private static function notify(NfcCardOrder $order, User $user, string $assetName, bool $replaced, bool $justCompleted): void
    {
        $to = config('mail.from.address');

        if (! $to) {
            return;
        }

        try {
            $files = self::files($order);
            Mail::to($to)->send(new SmartProductAssetsUploaded(
                $order,
                $user,
                array_map(fn ($a) => $a + ['url' => isset($files[$a['key']]) ? StorageHelper::url($files[$a['key']]['path']) : null], self::requirements($order)),
                $assetName,
                $replaced,
                $justCompleted,
            ));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * A product's asset list, cleaned up. Empty when the product doesn't take files.
     *
     * @return array<int, array{key: string, name: string, resolution: string, required: bool}>
     */
    public static function forDesign(?NfcCardDesign $design): array
    {
        if (! $design || $design->allow_logo_upload === false) {
            return [];
        }

        return self::clean((array) ($design->image_assets ?? []));
    }

    /** Copied into order_details.image_assets when the order is placed. */
    public static function snapshotFor(NfcCardDesign $design): array
    {
        return self::forDesign($design);
    }

    /** @return array<int, array{key: string, name: string, resolution: string, required: bool}> */
    public static function requirements(NfcCardOrder $order): array
    {
        $details = json_decode($order->order_details ?? '', true) ?: [];

        if (array_key_exists('image_assets', $details) && is_array($details['image_assets'])) {
            return self::clean($details['image_assets']);
        }

        return self::forDesign(NfcCardDesign::where('nfc_card_id', $order->nfc_card_id)->first());
    }

    /**
     * Uploaded files keyed by asset key. The old single logo fills in "logo"
     * (or the first asset) when nothing was uploaded for it.
     *
     * @return array<string, array{path: string, type: string, name: ?string, size: int, uploaded: ?string}>
     */
    public static function files(NfcCardOrder $order, ?array $requirements = null): array
    {
        $requirements ??= self::requirements($order);
        $files = [];

        if (Schema::hasTable('nfc_card_order_assets')) {
            foreach (NfcCardOrderAsset::where('nfc_card_order_id', $order->nfc_card_order_id)->get() as $asset) {
                $files[$asset->asset_key] = [
                    'path'     => $asset->file_path,
                    'type'     => $asset->file_type,
                    'name'     => $asset->original_name,
                    'size'     => $asset->size,
                    'uploaded' => $asset->updated_at?->toIso8601String(),
                ];
            }
        }

        $logoKey = collect($requirements)->firstWhere('key', 'logo')['key'] ?? ($requirements[0]['key'] ?? null);

        if ($logoKey && ! isset($files[$logoKey]) && $order->nfc_card_logo) {
            $files[$logoKey] = [
                'path'     => $order->nfc_card_logo,
                'type'     => self::typeOf($order->nfc_card_logo),
                'name'     => basename($order->nfc_card_logo),
                'size'     => 0,
                'uploaded' => null,
            ];
        }

        return $files;
    }

    /** @return array{total: int, uploaded: int, required: int, missingRequired: int} */
    public static function summary(NfcCardOrder $order): array
    {
        $requirements = self::requirements($order);
        $files        = self::files($order, $requirements);
        $required     = array_filter($requirements, fn ($a) => $a['required']);

        return [
            'total'           => count($requirements),
            'uploaded'        => count(array_filter($requirements, fn ($a) => isset($files[$a['key']]))),
            'required'        => count($required),
            'missingRequired' => count(array_filter($required, fn ($a) => ! isset($files[$a['key']]))),
        ];
    }

    public static function typeOf(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf' ? 'pdf' : 'image';
    }

    /**
     * Admin input → stored list. Keeps the keys of existing rows (so uploads
     * stay attached after a rename) and gives new rows a fresh key.
     */
    public static function fromInput(array $rows): array
    {
        $out  = [];
        $seen = [];

        foreach (array_slice($rows, 0, self::MAX_ASSETS) as $row) {
            $name = trim((string) ($row['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $key = preg_match('/^[A-Za-z0-9_-]{1,20}$/', (string) ($row['key'] ?? '')) ? $row['key'] : Str::lower(Str::random(6));

            while (isset($seen[$key])) {
                $key = Str::lower(Str::random(6));
            }

            $seen[$key] = true;
            $out[]      = [
                'key'        => $key,
                'name'       => mb_substr($name, 0, 80),
                'resolution' => mb_substr(trim((string) ($row['resolution'] ?? '')), 0, 120),
                'required'   => filter_var($row['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $out;
    }

    private static function clean(array $rows): array
    {
        return array_values(array_filter(array_map(fn ($row) => is_array($row) && ! empty($row['key']) && ! empty($row['name']) ? [
            'key'        => (string) $row['key'],
            'name'       => (string) $row['name'],
            'resolution' => (string) ($row['resolution'] ?? ''),
            'required'   => (bool) ($row['required'] ?? false),
        ] : null, $rows)));
    }
}
