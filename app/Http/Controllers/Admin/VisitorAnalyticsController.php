<?php

namespace App\Http\Controllers\Admin;

use App\AnalyticsMusicTrack;
use App\Helpers\StorageHelper;
use App\Http\Controllers\Controller;
use App\Services\Analytics\GeoIpUpdater;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Throwable;

/**
 * Visitor analytics settings: the MaxMind GeoLite2 account id and license key
 * (config "maxmind_account_id" / "maxmind_license_key"), the local database
 * status, and buttons to download it and fill in old visits. Also the mp3
 * tracks customers can play behind the analytics globe (React dashboard).
 */
class VisitorAnalyticsController extends Controller
{
    private const MAX_UPLOAD_KB = 15360;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $settings = GeoIpUpdater::settings();

        return Inertia::render('settings/visitor-analytics', [
            'accountId'     => $settings['account_id'],
            'hasLicenseKey' => $settings['license_key'] !== '',
            'lastUpdated'   => $settings['last_updated'] !== '' ? formatDateForUser($settings['last_updated']) : null,
            'database'      => GeoIpUpdater::status(),
            'unresolved'    => GeoIpUpdater::unresolvedCount(),
            'tracks'        => AnalyticsMusicTrack::orderBy('sort_order')->orderBy('id')->get()
                ->map(fn (AnalyticsMusicTrack $track) => [
                    'id'       => $track->id,
                    'title'    => $track->title,
                    'artist'   => $track->artist,
                    'src'      => $track->src(),
                    'uploaded' => $track->isUpload(),
                    'status'   => $track->status,
                ]),
            'maxUploadMb'   => self::MAX_UPLOAD_KB / 1024,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'account_id'  => 'required|digits_between:1,20',
            'license_key' => 'nullable|string|max:255',
        ], [], [
            'account_id'  => __('account ID'),
            'license_key' => __('license key'),
        ]);

        DB::table('config')->where('config_key', 'maxmind_account_id')->update(['config_value' => $data['account_id'], 'updated_at' => now()]);

        // Blank keeps the saved key, so the page never has to show it
        if (filled($data['license_key'] ?? null)) {
            DB::table('config')->where('config_key', 'maxmind_license_key')->update(['config_value' => trim($data['license_key']), 'updated_at' => now()]);
        }

        return back()->with('success', __('MaxMind settings saved.'));
    }

    public function download(GeoIpUpdater $updater)
    {
        try {
            $updater->update();
        } catch (Throwable $e) {
            return back()->with('failed', $e->getMessage());
        }

        return back()->with('success', __('GeoLite2 database downloaded.'));
    }

    public function backfill(GeoIpUpdater $updater)
    {
        try {
            $filled = $updater->backfill();
        } catch (Throwable $e) {
            return back()->with('failed', $e->getMessage());
        }

        return back()->with('success', __(':count old visits now have a location.', ['count' => $filled]));
    }

    public function storeTrack(Request $request)
    {
        $data = $this->validateTrack($request, true);

        AnalyticsMusicTrack::create([
            'title'      => $data['title'],
            'artist'     => $data['artist'] ?? null,
            'file_url'   => $this->trackFile($request) ?? $data['url'],
            'status'     => 1,
            'sort_order' => (int) AnalyticsMusicTrack::max('sort_order') + 1,
        ]);

        return back()->with('success', __('Track added.'));
    }

    public function updateTrack(Request $request, AnalyticsMusicTrack $track)
    {
        $data = $this->validateTrack($request, false);

        $file = $this->trackFile($request);
        $newUrl = $file ?? ($data['url'] ?? null);
        if ($newUrl && $newUrl !== $track->file_url && $track->isUpload()) {
            StorageHelper::deleteFile($track->file_url);
        }

        $track->update([
            'title'    => $data['title'],
            'artist'   => $data['artist'] ?? null,
            'file_url' => $newUrl ?: $track->file_url,
        ]);

        return back()->with('success', __('Track updated.'));
    }

    public function toggleTrack(AnalyticsMusicTrack $track)
    {
        $track->update(['status' => !$track->status]);

        return back()->with('success', $track->status ? __('Track shown to customers.') : __('Track hidden from customers.'));
    }

    /** Swap with the neighbour above or below. */
    public function moveTrack(Request $request, AnalyticsMusicTrack $track)
    {
        $up = $request->input('direction') === 'up';
        $neighbour = AnalyticsMusicTrack::where('sort_order', $up ? '<' : '>', $track->sort_order)
            ->orderBy('sort_order', $up ? 'desc' : 'asc')
            ->first();

        if ($neighbour) {
            [$track->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $track->sort_order];
            $track->save();
            $neighbour->save();
        }

        return back();
    }

    public function deleteTrack(AnalyticsMusicTrack $track)
    {
        if ($track->isUpload()) {
            StorageHelper::deleteFile($track->file_url);
        }
        $track->delete();

        return back()->with('success', __('Track deleted.'));
    }

    /** A new track needs an upload or a URL; an edit may keep its current file. */
    private function validateTrack(Request $request, bool $creating): array
    {
        return $request->validate([
            'title'  => 'required|string|max:150',
            'artist' => 'nullable|string|max:150',
            'file'   => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/mp3', 'max:' . self::MAX_UPLOAD_KB],
            'url'    => [$creating ? 'required_without:file' : 'nullable', 'nullable', 'url:https', 'max:2048'],
        ], [
            'url.required_without' => __('Upload an mp3 or paste a link to one.'),
            'file.mimetypes'       => __('Only mp3 files can be uploaded.'),
        ], [
            'title' => __('title'),
            'file'  => __('mp3 file'),
            'url'   => __('link'),
        ]);
    }

    private function trackFile(Request $request): ?string
    {
        if (!$request->hasFile('file')) {
            return null;
        }

        return StorageHelper::uploadImage($request->file('file'), 'analytics-music', 'track-' . Str::random(12) . '.mp3');
    }
}
