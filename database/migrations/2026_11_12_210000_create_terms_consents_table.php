<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terms_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('terms_version', 50);
            $table->timestamp('consented_at');
            $table->string('consent_method', 40);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['user_id', 'terms_version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terms_consents');
    }
};
