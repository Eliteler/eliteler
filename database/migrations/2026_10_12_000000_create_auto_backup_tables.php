<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables of the AutoBackup plugin. The plugin also creates them itself on first use
 * (plugins/AutoBackup/Support/Tables.php), so both are guarded with hasTable.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auto_backup_destinations')) {
            Schema::create('auto_backup_destinations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('driver', 50)->index();
                $table->longText('credentials')->nullable();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamp('last_checked_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auto_backup_settings')) {
            Schema::create('auto_backup_settings', function (Blueprint $table) {
                $table->id();
                $table->boolean('enabled')->default(false);
                $table->string('frequency', 30)->default('daily');
                $table->time('run_at')->default('02:00:00');
                $table->unsignedTinyInteger('day_of_week')->nullable();
                $table->unsignedTinyInteger('day_of_month')->nullable();
                $table->boolean('include_files')->default(true);
                $table->boolean('include_database')->default(true);
                $table->json('excluded_paths')->nullable();
                $table->string('notify_email')->nullable();
                $table->timestamp('last_scheduled_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auto_backup_runs')) {
            Schema::create('auto_backup_runs', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('trigger', 20);
                $table->string('status', 20)->index();
                $table->boolean('include_files')->default(true);
                $table->boolean('include_database')->default(true);
                $table->unsignedBigInteger('started_by')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->unsignedBigInteger('total_size')->default(0);
                $table->text('error')->nullable();
                $table->timestamps();
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('auto_backup_files')) {
            Schema::create('auto_backup_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('run_id')->constrained('auto_backup_runs')->cascadeOnDelete();
                $table->string('kind', 20);
                $table->string('file_name');
                $table->unsignedBigInteger('size')->default(0);
                $table->string('checksum', 64)->nullable();
                $table->string('local_path')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auto_backup_uploads')) {
            Schema::create('auto_backup_uploads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('file_id')->constrained('auto_backup_files')->cascadeOnDelete();
                $table->foreignId('destination_id')->constrained('auto_backup_destinations')->cascadeOnDelete();
                $table->string('status', 20)->default('pending');
                $table->string('remote_id')->nullable();
                $table->text('remote_url')->nullable();
                $table->unsignedInteger('attempts')->default(0);
                $table->text('error')->nullable();
                $table->timestamp('uploaded_at')->nullable();
                $table->timestamps();
                $table->unique(['file_id', 'destination_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_backup_uploads');
        Schema::dropIfExists('auto_backup_files');
        Schema::dropIfExists('auto_backup_runs');
        Schema::dropIfExists('auto_backup_settings');
        Schema::dropIfExists('auto_backup_destinations');
    }
};
