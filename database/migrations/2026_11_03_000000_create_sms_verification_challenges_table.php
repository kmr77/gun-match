<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_verification_challenges', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('phone_hash')->nullable();
            $table->string('code_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('window_started_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('sends_in_window')->default(0);
            $table->timestamps();
            $table->index('last_sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_verification_challenges');
    }
};
