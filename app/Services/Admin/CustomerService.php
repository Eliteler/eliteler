<?php
namespace App\Services\Admin;

use App\BusinessCard;
use App\BusinessField;
use App\BusinessHour;
use App\EmailTemplate;
use App\Gallery;
use App\Payment;
use App\Service;
use App\StoreProduct;
use App\Support\Admin\AdminPermissions;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Customer accounts (users.role_id = 2).
 *
 * users.status: 1 active, 0 inactive, 2 suspended, -1 deleted by the customer
 * (their email gets a "deleted" suffix, see User\AccountController). Email
 * verification is users.email_verified_at.
 */
class CustomerService
{
    public const TABS = ['all', 'verified', 'unverified', 'inactive', 'banned', 'deleted'];

    public const SORTS = ['newest', 'oldest', 'name', 'expiring'];

    private const WELCOME_TEMPLATE = '584922675208';

    private const RESET_PASSWORD_TEMPLATE = '584922675212';

    public function query(): Builder
    {
        return User::where('role_id', 2);
    }

    public function applyTab(Builder $query, string $tab): Builder
    {
        return match ($tab) {
            'verified'   => $query->whereNotNull('email_verified_at')->where('status', 1),
            'unverified' => $query->whereNull('email_verified_at')->where('status', 1),
            'inactive'   => $query->where('status', 0),
            'banned'     => $query->where('status', 2),
            'deleted'    => $query->where('status', -1),
            default      => $query->where('status', '!=', -1),
        };
    }

    /** @return array<string, int> */
    public function tabCounts(?Builder $query = null): array
    {
        $row = ($query ?? $this->query())
            ->reorder()
            ->selectRaw('SUM(status != -1) as `all`')
            ->selectRaw('SUM(status = 1 AND email_verified_at IS NOT NULL) as verified')
            ->selectRaw('SUM(status = 1 AND email_verified_at IS NULL) as unverified')
            ->selectRaw('SUM(status = 0) as inactive')
            ->selectRaw('SUM(status = 2) as banned')
            ->selectRaw('SUM(status = -1) as deleted')
            ->first();

        return collect(self::TABS)
            ->mapWithKeys(fn($tab) => [$tab => (int) ($row->{$tab} ?? 0)])
            ->all();
    }

    /**
     * @param  array{search: string, plan: string, subscription: string, sort: string}  $filters
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $filters['search'];

        return $query
            ->when($search !== '', fn($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('user_id', $search);
            }))
            ->when($filters['plan'] === 'none', fn($q) => $q->whereNull('plan_id'))
            ->when(! in_array($filters['plan'], ['all', 'none'], true), fn($q) => $q->where('plan_id', $filters['plan']))
            ->when($filters['subscription'] === 'active', fn($q) => $q->where('plan_validity', '>=', Carbon::now()))
            ->when($filters['subscription'] === 'expired', fn($q) => $q->where('plan_validity', '<', Carbon::now()))
            ->when($filters['subscription'] === 'expiring', fn($q) => $q->whereBetween('plan_validity', [Carbon::now(), Carbon::now()->addDays(7)->endOfDay()]))
            ->when($filters['sort'] === 'oldest', fn($q) => $q->orderBy('id'))
            ->when($filters['sort'] === 'name', fn($q) => $q->orderBy('name'))
            ->when($filters['sort'] === 'expiring', fn($q) => $q->orderByRaw('plan_validity IS NULL, plan_validity'))
            ->when($filters['sort'] === 'newest', fn($q) => $q->orderByDesc('id'));
    }

    /** Number of non-deleted business cards and stores per customer, as subqueries. */
    public function withCardCounts(Builder $query): Builder
    {
        $count = fn(string $type) => DB::table('business_cards')
            ->selectRaw('COUNT(*)')
            ->whereColumn('business_cards.user_id', 'users.user_id')
            ->where('card_type', $type)
            ->where('card_status', '!=', 'deleted');

        return $query->addSelect([
            'vcards_count' => $count('vcard'),
            'stores_count' => $count('store'),
        ]);
    }

    /**
     * Plan summary from users.plan_details (snapshot of the plan when it was assigned).
     */
    public function planSummary(User $user): ?array
    {
        $plan = json_decode($user->plan_details ?? '', true);

        if (! $plan) {
            return null;
        }

        $expires = $user->plan_validity ? Carbon::parse($user->plan_validity) : null;

        return [
            'id'          => $user->plan_id,
            'name'        => $plan['plan_name'] ?? __('Unknown plan'),
            'price'       => AdminPermissions::price((float) ($plan['plan_price'] ?? 0)),
            'validity'    => (int) ($plan['validity'] ?? $user->term ?? 0),
            'activatedAt' => $user->plan_activation_date ? Carbon::parse($user->plan_activation_date)->toIso8601String() : null,
            'expiresAt'   => $expires?->toIso8601String(),
            'expired'     => $expires ? $expires->isPast() : false,
            'daysLeft'    => $expires && $expires->isFuture() ? (int) ceil(Carbon::now()->diffInDays($expires)) : 0,
        ];
    }

    public function state(User $user): string
    {
        return match ((int) $user->status) {
            0       => 'inactive',
            2       => 'suspended',
            -1      => 'deleted',
            default => 'active',
        };
    }

    public function create(array $data): User
    {
        $user                    = new User();
        $user->user_id           = uniqid();
        $user->role_id           = 2;
        $user->name              = ucwords(strtolower($data['full_name']));
        $user->email             = $data['email'];
        $user->email_verified_at = now();
        $user->password          = Hash::make($data['password']);
        $user->status            = (int) $data['status'];

        return $user;
    }

    public function update(User $user, array $data): void
    {
        $user->name   = $data['full_name'];
        $user->email  = $data['email'];
        $user->status = (int) $data['status'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
    }

    /**
     * Account state changes from the admin panel.
     *
     * verify / unverify keep the account active; suspend disables all the
     * customer's cards; activate / unsuspend re-enable them.
     */
    public function changeState(User $user, string $action): void
    {
        DB::transaction(function () use ($user, $action) {
            $cardsStatus = null;

            switch ($action) {
                case 'verify':
                    $user->email_verified_at = $user->email_verified_at ?? Carbon::now();
                    $user->status            = 1;
                    $cardsStatus             = 1;
                    break;
                case 'unverify':
                    $user->email_verified_at = null;
                    $user->status            = 1;
                    $cardsStatus             = 1;
                    break;
                case 'suspend':
                    $user->status = 2;
                    $cardsStatus  = 0;
                    break;
                case 'activate':
                case 'unsuspend':
                    $user->status = 1;
                    $cardsStatus  = 1;
                    break;
                case 'deactivate':
                    $user->status = 0;
                    $cardsStatus  = 0;
                    break;
            }

            $user->save();

            if ($cardsStatus !== null) {
                BusinessCard::where('user_id', $user->user_id)->update(['status' => $cardsStatus]);
            }
        });
    }

    /**
     * Restore an account the customer deleted. Returns false when the original
     * email address has been taken by another account since.
     */
    public function restore(User $user): bool
    {
        $email = preg_replace('/deleted$/', '', $user->email);

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return false;
        }

        $user->email  = $email;
        $user->status = 1;
        $user->save();

        return true;
    }

    /**
     * Permanently delete a customer with their business cards and stores.
     * Payment records (transactions) are kept for accounting.
     */
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $cardIds = BusinessCard::where('user_id', $user->user_id)->pluck('card_id');

            foreach ([BusinessField::class, BusinessHour::class, Gallery::class, Payment::class, Service::class, StoreProduct::class] as $model) {
                $model::whereIn('card_id', $cardIds)->delete();
            }

            BusinessCard::where('user_id', $user->user_id)->delete();
            $user->delete();
        });
    }

    public function sendWelcomeEmail(User $user): void
    {
        $template = EmailTemplate::where('email_template_id', self::WELCOME_TEMPLATE)->first();

        if (! $template) {
            return;
        }

        try {
            Mail::to($user->email)
                ->bcc(config('mail.from.address') ?? env('MAIL_FROM_ADDRESS'))
                ->send(new \App\Mail\AppointmentMail([
                    'status'          => '',
                    'emailSubject'    => $template->email_template_subject,
                    'emailContent'    => $template->email_template_content,
                    'registeredName'  => ucfirst($user->name),
                    'registeredEmail' => $user->email,
                ]));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Email a password reset link. Returns false if the email could not be sent.
     */
    public function sendPasswordResetEmail(User $user): bool
    {
        $template = EmailTemplate::where('email_template_id', self::RESET_PASSWORD_TEMPLATE)->first();

        if (! $template) {
            return false;
        }

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        try {
            Mail::to($user->email)->send(new \App\Mail\AppointmentMail([
                'emailSubject' => $template->email_template_subject,
                'emailContent' => $template->email_template_content,
                'appname'      => config('app.name'),
                'actionlink'   => url(route('password.reset', ['token' => $token, 'email' => $user->email], false)),
            ]));

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
