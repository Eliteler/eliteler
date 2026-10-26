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

namespace Plugins\AiBuilder\Observers;

use App\Transaction;
use App\User;
use Illuminate\Support\Facades\DB;

class AiBuilderObserver
{
    public function created($model)
    {
        // return;
    }

    public function updated($model)
    {
        // check if plan details is changed
        try {
            if ($model->wasChanged('plan_activation_date')) {
                
                // Get user
                $user = $model;

                // Get or create AI credits record
                $ai_credits = DB::table('ai_credits')
                    ->where('user_id', $user->user_id)
                    ->first();

                if (! $ai_credits) {
                    // Insert AI credits record
                    DB::table('ai_credits')->insert([
                        'user_id' => $user->user_id,
                        'credits'       => 0,
                        'trial_credits' => 0,
                    ]);

                    // Get AI credits
                    $ai_credits = (object) [
                        'credits'       => 0,
                        'trial_credits' => 0,
                    ];
                }

                // transaction
                $transaction = Transaction::where('user_id', $user->id)->latest()->first();

                // check if trial
                $is_trial = optional($transaction)->payment_gateway_name == 'TRIAL';

                // Get plan details
                $newPlan = json_decode($model->plan_details, true) ?: [];

                if ($is_trial) {
                    // Update credits
                    DB::table('ai_credits')
                        ->where('user_id', $user->user_id)
                        ->update([
                            'trial_credits' => (int) ($newPlan['trial_ai_credits'] ?? 0),
                        ]);
                } else {
                    // Plan credits
                    $new_credits = (int) ($newPlan['ai_credits'] ?? 0);

                    // Unlimited credits
                    if ($new_credits === -1) {
                        DB::table('ai_credits')
                            ->where('user_id', $user->user_id)
                            ->update([
                                'credits'       => -1,
                                'trial_credits' => 0,
                            ]);
                    } else {
                        // Existing credits
                        $existing_credits = (int) ($ai_credits->credits ?? 0);

                        // If previously unlimited, reset to 0 before adding
                        if ($existing_credits === -1) {
                            $existing_credits = 0;
                        }

                        // Update credits
                        DB::table('ai_credits')
                            ->where('user_id', $user->user_id)
                            ->update([
                                'credits'       => $existing_credits + $new_credits,
                                'trial_credits' => 0,
                            ]);
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }
}
