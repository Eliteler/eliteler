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

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
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
    public function roles()
    {
        return $this->belongsTo('App\Models\Role');
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
}
