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

namespace App\Classes;

use Illuminate\Support\Facades\DB;

class InvoiceNumber
{
    // Take the next invoice number for the table and prefix, and save it with $save($invoiceNumber)
    // inside one locked transaction, so parallel payments can't get the same number.
    // Numbering restarts per prefix.
    public static function assign(string $table, ?string $prefix, callable $save): int
    {
        return DB::transaction(function () use ($table, $prefix, $save) {
            $invoiceNumber = self::next($table, $prefix);

            $save($invoiceNumber);

            return $invoiceNumber;
        });
    }

    // Must be called inside a DB transaction that also saves the number:
    // the config row lock is held until commit.
    public static function next(string $table, ?string $prefix): int
    {
        DB::table('config')->where('config_key', 'invoice_prefix')->lockForUpdate()->first();

        return (int) DB::table($table)
            ->where('invoice_prefix', $prefix)
            ->max(DB::raw('CAST(invoice_number AS UNSIGNED)')) + 1;
    }
}
