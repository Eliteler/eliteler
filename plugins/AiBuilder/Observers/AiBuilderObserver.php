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

namespace Plugins\AiBuilder\Observers;


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
            if ($model instanceof User && $model->isDirty('plan_details')) {
                // user
                $user = $model;

                // get new plan details
                $newPlan = json_decode($model->plan_details, true);

                // ai credits
                $ai_credits = DB::table('ai_credits')
                    ->where('user_id', $user->user_id)
                    ->first();

                // unused credits
                $unused_credits = (int) ($ai_credits ? $ai_credits->credits : 0);

                // new credits
                $new_credits = (int) ($newPlan['ai_credits'] ?? 0);

                if (empty($ai_credits)) {
                    // insert new credits
                    DB::table('ai_credits')->insert([
                        'user_id' => $user->user_id,
                        'credits' => $new_credits,
                    ]);
                } else {
                    // update credits
                    DB::table('ai_credits')
                        ->where('user_id', $user->user_id)
                        ->update([
                            'credits' => $unused_credits + $new_credits,
                        ]);
                }
            }
        } catch (\Exception $e) {
        }
    }
}
