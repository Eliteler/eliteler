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

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'user_id' => '609c03880ee47',
            'role_id' => '1',
            'permissions' => '{"in_app_purchases":1,"blogs":1,"web_templates":1,"plans":1,"users":1,"themes":1,"business_card_intros":1,"sitemap":1,"customers":1,"invoice_tax":1,"transactions":1,"translations":1,"payment_methods":1,"software_update":1,"general_settings":1,"coupons":1,"custom_domain":1,"marketing":1,"maintenance_mode":1,"demo_mode":1,"backup":1}',
            'name' => 'GoBiz',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin@admin'),
            'auth_type' => 'Email',
        ]);
    }
}

