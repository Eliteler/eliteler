<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AutoBackup 1.5.0: how many backups to keep in storage (null = all).
 * The plugin also adds the column itself (plugins/AutoBackup/Support/Tables.php), so it is guarded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auto_backup_settings') && ! Schema::hasColumn('auto_backup_settings', 'keep_last')) {
            Schema::table('auto_backup_settings', function (Blueprint $table) {
                $table->unsignedInteger('keep_last')->nullable()->after('excluded_paths');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('auto_backup_settings', 'keep_last')) {
            Schema::table('auto_backup_settings', function (Blueprint $table) {
                $table->dropColumn('keep_last');
            });
        }
    }
};
