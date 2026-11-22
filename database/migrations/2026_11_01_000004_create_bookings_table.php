<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained()->cascadeOnDelete();
            $table->string('car_type', 100);
            $table->string('plate', 15);
            $table->string('period', 10);
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_price');
            $table->string('status', 25)->default('menunggu_konfirmasi');
            $table->timestamp('expires_at')->nullable();
            $table->string('payment_proof_path')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('reject_reason')->nullable();
            $table->string('terminate_reason')->nullable();
            $table->unsignedInteger('arrears_amount')->default(0);
            $table->timestamp('agreed_at');
            $table->timestamps();

            // dipakai cek bentrok: slot + rentang tanggal
            $table->index(['slot_id', 'start_date', 'end_date']);
            $table->index(['status', 'expires_at']);
            $table->index('plate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
