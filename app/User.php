<?php

/*
 |--------------------------------------------------------------------------
 | Eliteler vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App;

use App\Plan;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyNewEmail, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'lang',
        'profile_image',
        'name',
        'email',
        'country_code',
        'mobile_number',
        'email_verified_at',
        'auth_type',
        'password',
        'auth_token',
        'device_token',
        'plan_id',
        'term',
        'plan_validity',
        'plan_activation_date',
        'plan_details',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Define relationship with Role model.
     */
    public function role()
    {
        return $this->belongsTo('App\Role', 'role_id', 'role_id');
    }

    /**
     * Send new email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        try {
            // Retrieve config by key instead of index for safer access
            $config = DB::table('config')->pluck('config_value', 'config_key');

            if (($config['disable_user_email_verification'] ?? '0') == '1') {
                try {
                    // Get Welcome email template
                    $emailTemplateDetails = EmailTemplate::where('email_template_id', '584922675213')->first();

                    // Generate signed verification link
                    $verificationUrl = URL::temporarySignedRoute(
                        'verification.verify.public',
                        now()->addMinutes(60),
                        [
                            'id' => $this->id,
                            'hash' => sha1($this->email),
                        ]
                    );

                    $message = [
                        'status' => "",
                        'emailSubject' => $emailTemplateDetails->email_template_subject,
                        'emailContent' => $emailTemplateDetails->email_template_content,
                        'actionlink' => $verificationUrl,
                    ];

                    // Send email (using built-in Mailable or custom one)
                    Mail::to($this->email)->send(new \App\Mail\AppointmentMail($message));

                    // $this->newEmail($this->getEmailForVerification());
                } catch (\Exception $e) {
                    Log::error('Email verification notification failed', [
                        'user_id' => $this->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Email verification notification failed', [
                'user_id' => $this->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::deleting(function ($user) {
            // Helper function to remove a file from server
            $deletePhysicalFile = function ($filePath) {
                if (empty($filePath)) {
                    return;
                }

                // Handle JSON format (e.g., covers array)
                if (is_string($filePath) && (str_starts_with($filePath, '[') || str_starts_with($filePath, '{'))) {
                    $decoded = json_decode($filePath, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $item) {
                            if (is_string($item)) {
                                $clean = ltrim(parse_url($item, PHP_URL_PATH), '/');
                                $full = public_path($clean);
                                if (File::exists($full) && is_file($full)) {
                                    File::delete($full);
                                }
                            }
                        }
                        return;
                    }
                }

                $clean = ltrim(parse_url($filePath, PHP_URL_PATH), '/');
                $full = public_path($clean);
                if (File::exists($full) && is_file($full)) {
                    File::delete($full);
                }
            };

            // Collect all user IDs
            $userIds = array_values(array_unique(array_filter([
                (string) $user->id,
                (string) $user->user_id,
            ])));

            // 1. Delete user profile image
            $deletePhysicalFile($user->profile_image);

            // 2. Fetch business card IDs
            $cardIds = DB::table('business_cards')
                ->whereIn('user_id', $userIds)
                ->pluck('card_id')
                ->toArray();

            // 3. Delete files linked to cards
            if (!empty($cardIds)) {
                $cards = DB::table('business_cards')->whereIn('card_id', $cardIds)->get(['cover', 'profile']);
                foreach ($cards as $card) {
                    $deletePhysicalFile($card->cover);
                    $deletePhysicalFile($card->profile);
                }

                $galleries = DB::table('galleries')->whereIn('card_id', $cardIds)->pluck('gallery_image');
                foreach ($galleries as $gallery) {
                    $deletePhysicalFile($gallery);
                }

                $services = DB::table('services')->whereIn('card_id', $cardIds)->pluck('service_image');
                foreach ($services as $service) {
                    $deletePhysicalFile($service);
                }

                $storeProducts = DB::table('store_products')->whereIn('card_id', $cardIds)->pluck('product_image');
                foreach ($storeProducts as $sp) {
                    $deletePhysicalFile($sp);
                }

                $vcardProducts = DB::table('vcard_products')->whereIn('card_id', $cardIds)->pluck('product_image');
                foreach ($vcardProducts as $vp) {
                    $deletePhysicalFile($vp);
                }

                $payments = DB::table('payments')->whereIn('card_id', $cardIds)->where('type', 'image')->pluck('content');
                foreach ($payments as $payment) {
                    $deletePhysicalFile($payment);
                }

                $testimonials = DB::table('testimonials')->whereIn('card_id', $cardIds)->pluck('reviewer_image');
                foreach ($testimonials as $t) {
                    $deletePhysicalFile($t);
                }

                $infoPops = DB::table('information_pops')->whereIn('card_id', $cardIds)->pluck('info_pop_image');
                foreach ($infoPops as $pop) {
                    $deletePhysicalFile($pop);
                }
            }

            // 4. Delete other files
            $medias = DB::table('medias')->whereIn('user_id', $userIds)->pluck('media_url');
            foreach ($medias as $media) {
                $deletePhysicalFile($media);
            }

            $storeCats = DB::table('store_categories')->whereIn('user_id', $userIds)->pluck('thumbnail');
            foreach ($storeCats as $sc) {
                $deletePhysicalFile($sc);
            }

            $categories = DB::table('categories')->whereIn('user_id', $userIds)->pluck('thumbnail');
            foreach ($categories as $cat) {
                $deletePhysicalFile($cat);
            }

            $nfcLogos = DB::table('nfc_card_orders')->whereIn('user_id', $userIds)->pluck('nfc_card_logo');
            foreach ($nfcLogos as $logo) {
                $deletePhysicalFile($logo);
            }

            // 5. Delete database records
            DB::table('ai_credits')->whereIn('user_id', $userIds)->delete();
            DB::table('ai_credits_transactions')->whereIn('user_id', $userIds)->delete();

            if (!empty($cardIds)) {
                DB::table('business_fields')->whereIn('card_id', $cardIds)->delete();
                DB::table('business_hours')->whereIn('card_id', $cardIds)->delete();
                DB::table('card_appointment_times')->whereIn('card_id', $cardIds)->delete();
                DB::table('booked_appointments')->whereIn('card_id', $cardIds)->delete();
                DB::table('contact_forms')->whereIn('card_id', $cardIds)->delete();
                DB::table('galleries')->whereIn('card_id', $cardIds)->delete();
                DB::table('information_pops')->whereIn('card_id', $cardIds)->delete();
                DB::table('newsletters')->whereIn('card_id', $cardIds)->delete();
                DB::table('payments')->whereIn('card_id', $cardIds)->delete();
                DB::table('services')->whereIn('card_id', $cardIds)->delete();
                DB::table('service_bookings')->whereIn('vcard_id', $cardIds)->delete();
                DB::table('service_booking_confirmeds')->whereIn('vcard_id', $cardIds)->delete();
                DB::table('store_products')->whereIn('card_id', $cardIds)->delete();
                DB::table('testimonials')->whereIn('card_id', $cardIds)->delete();
                DB::table('vcard_products')->whereIn('card_id', $cardIds)->delete();
                DB::table('visitors')->whereIn('card_id', $cardIds)->delete();
                DB::table('google_wallets_vcard')->whereIn('card_id', $cardIds)->delete();
                DB::table('custom_domain_requests')->whereIn('card_id', $cardIds)->delete();

                DB::table('nfc_card_keys')
                    ->whereIn('card_id', $cardIds)
                    ->update([
                        'card_id' => null,
                        'link_status' => 'unlinked',
                        'updated_at' => now(),
                    ]);

                DB::table('business_cards')->whereIn('card_id', $cardIds)->delete();
            }

            DB::table('store_business_hours')->whereIn('user_id', $userIds)->delete();
            DB::table('store_categories')->whereIn('user_id', $userIds)->delete();
            DB::table('store_orders')->whereIn('user_id', $userIds)->delete();

            DB::table('contact_forms')->whereIn('user_id', $userIds)->delete();
            DB::table('service_bookings')->whereIn('user_id', $userIds)->delete();
            DB::table('service_booking_confirmeds')->whereIn('user_id', $userIds)->delete();
            DB::table('categories')->whereIn('user_id', $userIds)->delete();
            DB::table('medias')->whereIn('user_id', $userIds)->delete();
            DB::table('google_wallets')->whereIn('user_id', $userIds)->delete();
            DB::table('custom_domain_requests')->whereIn('user_id', $userIds)->delete();

            $nfcOrders = DB::table('nfc_card_orders')->whereIn('user_id', $userIds)->pluck('nfc_card_order_id')->toArray();
            if (!empty($nfcOrders)) {
                DB::table('nfc_card_order_transactions')->whereIn('nfc_card_order_id', $nfcOrders)->delete();
                DB::table('nfc_card_orders')->whereIn('nfc_card_order_id', $nfcOrders)->delete();
            }

            $referralRequests = DB::table('referral_withdraw_requests')->whereIn('user_id', $userIds)->pluck('referral_withdraw_request_id')->toArray();
            if (!empty($referralRequests)) {
                DB::table('referral_withdraw_transactions')->whereIn('referral_withdraw_request_id', $referralRequests)->delete();
                DB::table('referral_withdraw_requests')->whereIn('referral_withdraw_request_id', $referralRequests)->delete();
            }

            DB::table('referrals')
                ->whereIn('user_id', $userIds)
                ->orWhereIn('referred_user_id', $userIds)
                ->delete();

            DB::table('applied_coupons')->whereIn('user_id', $userIds)->delete();
            DB::table('in_app_purchases')->whereIn('user_id', $userIds)->delete();
            DB::table('transactions')->whereIn('user_id', $userIds)->delete();

            DB::table('pending_user_emails')->where('user_id', $user->id)->delete();
            DB::table('password_resets')->where('email', $user->email)->delete();
        });
    }
}
