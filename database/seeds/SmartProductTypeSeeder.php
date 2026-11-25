<?php

namespace Database\Seeders;

use App\Support\SmartProducts\SmartProductsSchema;
use Illuminate\Database\Seeder;

class SmartProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        SmartProductsSchema::seedProductTypes();
    }
}
