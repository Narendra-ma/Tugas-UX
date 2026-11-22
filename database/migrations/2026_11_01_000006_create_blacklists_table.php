<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('plate', 15);
            $table->string('reason');
            $table->unsignedInteger('arrears_amount')->default(0);
            $table->timestamps();

            $table->index(['owner_id', 'plate']);
            $table->index(['owner_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklists');
    }
};
