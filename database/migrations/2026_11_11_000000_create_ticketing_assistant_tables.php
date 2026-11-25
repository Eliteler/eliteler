<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TicketingSystem AI Assistant: customers' chats with it, their messages, and the assistant's
 * instruction files. The plugin also creates them itself
 * (plugins/TicketingSystem/Support/Tables.php), so each one is guarded.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ticketing_assistant_chats')) {
            Schema::create('ticketing_assistant_chats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('title', 191);
                $table->unsignedBigInteger('ticket_id')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticketing_assistant_messages')) {
            Schema::create('ticketing_assistant_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chat_id')->index();
                $table->string('role', 10);
                $table->longText('body');
                $table->boolean('needs_human')->default(false);
                $table->string('provider', 20)->nullable();
                $table->string('model', 100)->nullable();
                $table->unsignedInteger('input_tokens')->default(0);
                $table->unsignedInteger('output_tokens')->default(0);
                $table->text('error')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ticketing_assistant_files')) {
            Schema::create('ticketing_assistant_files', function (Blueprint $table) {
                $table->id();
                $table->string('path', 255);
                $table->string('original_name', 191);
                $table->unsignedInteger('size');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticketing_assistant_files');
        Schema::dropIfExists('ticketing_assistant_messages');
        Schema::dropIfExists('ticketing_assistant_chats');
    }
};
