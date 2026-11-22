<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('city', 100);
            $table->string('address');
            $table->text('description')->nullable();
            $table->string('size_note', 100)->nullable();
            $table->unsignedInteger('price_daily');
            $table->unsignedInteger('price_weekly');
            $table->unsignedInteger('price_monthly');
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'city']);
            $table->index('price_monthly');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garages');
    }
};
