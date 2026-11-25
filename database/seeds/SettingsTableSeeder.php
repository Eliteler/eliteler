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

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            "google_key" => "",
            "google_analytics_id" => "",
            "site_name" => "GoBiz",
            "site_logo" => "img/logo.png",
            "favicon" => "img/favicon.png",
            // Mail settings (required columns; changed later on the SMTP settings page)
            "name" => "GoBiz",
            "address" => "",
            "driver" => "smtp",
            "host" => "",
            "port" => 587,
            "encryption" => "tls",
        ]);
    }
}
