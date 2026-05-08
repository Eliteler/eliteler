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

namespace App\Providers;

use App\Classes\Migrate602;

class MigrationConfig
{
    public function alteration()
    {
        $migrate = new Migrate602;
        $migrate->tableCreation();
        
        return;
    }
}
