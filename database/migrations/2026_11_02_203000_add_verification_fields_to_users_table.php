<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('verification_status', 20)->default('unverified')->index();
            $table->string('verification_type', 20)->nullable();
            $table->string('verification_method', 255)->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('verification_note')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by_user_id');
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_status',
                'verification_type',
                'verification_method',
                'verified_at',
                'reviewed_at',
                'verification_note',
            ]);
        });
    }
};
