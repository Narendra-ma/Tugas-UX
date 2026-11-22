<?php

namespace App\Services;

use App\Models\Blacklist;
use App\Models\Booking;
use App\Models\Garage;
use App\Models\Slot;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    /** Batas respon pemilik dan batas upload bukti bayar (aturan 1x24 jam dari laporan UX). */
    public const HOLD_HOURS = 24;

    public function total(Garage $garage, string $period, string $start, string $end): int
    {
        $days = (int) Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;

        $units = match ($period) {
            'daily' => $days,
            'weekly' => (int) ceil($days / 7),
            'monthly' => (int) ceil($days / 30),
        };

        return $units * $garage->priceFor($period);
    }

    public function isFree(Slot $slot, string $start, string $end): bool
    {
        return ! $slot->bookings()->blocking()->overlapping($start, $end)->exists();
    }

    public function create(User $user, Slot $slot, array $data): Booking
    {
        return DB::transaction(function () use ($user, $slot, $data) {
            // kunci baris slot supaya dua request bersamaan menunggu giliran
            $slot = Slot::with('garage')->lockForUpdate()->findOrFail($slot->id);

            if (! $slot->is_active || ! $this->isFree($slot, $data['start_date'], $data['end_date'])) {
                throw ValidationException::withMessages([
                    'slot_id' => 'Slot ini sudah dipesan di tanggal tersebut. Coba slot atau tanggal lain.',
                ]);
            }

            $booking = new Booking([
                'slot_id' => $slot->id,
                'car_type' => $data['car_type'],
                'plate' => $data['plate'],
                'period' => $data['period'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_price' => $this->total($slot->garage, $data['period'], $data['start_date'], $data['end_date']),
                'status' => 'menunggu_konfirmasi',
                'expires_at' => now()->addHours(self::HOLD_HOURS),
                'agreed_at' => now(),
            ]);
            $booking->user_id = $user->id;
            $booking->save();

            return $booking;
        });
    }

    public function accept(Booking $booking): void
    {
        $this->must($booking, 'menunggu_konfirmasi');

        $booking->update([
            'status' => 'menunggu_bayar',
            'expires_at' => now()->addHours(self::HOLD_HOURS),
        ]);
    }

    public function reject(Booking $booking, string $reason): void
    {
        $this->must($booking, 'menunggu_konfirmasi');

        $booking->update(['status' => 'ditolak', 'reject_reason' => $reason, 'expires_at' => null]);
    }

    /** Penyewa hanya boleh batal selama pemilik belum konfirmasi. */
    public function cancel(Booking $booking): void
    {
        $this->must($booking, 'menunggu_konfirmasi');

        $booking->update(['status' => 'batal', 'expires_at' => null]);
    }

    public function uploadProof(Booking $booking, UploadedFile $file): void
    {
        $this->must($booking, 'menunggu_bayar');

        // disimpan di disk private, nama file diacak oleh Laravel
        $booking->update(['payment_proof_path' => $file->store('proofs')]);
    }

    public function markPaid(Booking $booking): void
    {
        $this->must($booking, 'menunggu_bayar');

        $booking->update(['status' => 'dibayar', 'paid_at' => now(), 'expires_at' => null]);
    }

    public function finish(Booking $booking): void
    {
        $this->must($booking, 'dibayar');

        $booking->update(['status' => 'selesai']);
    }

    public function terminate(Booking $booking, string $reason, int $arrears): void
    {
        $this->must($booking, 'dibayar');

        DB::transaction(function () use ($booking, $reason, $arrears) {
            $booking->update([
                'status' => 'diputus',
                'terminate_reason' => $reason,
                'arrears_amount' => $arrears,
            ]);

            $booking->loadMissing('user', 'slot.garage');

            $entry = new Blacklist([
                'booking_id' => $booking->id,
                'name' => $booking->user->name,
                'phone' => $booking->user->phone,
                'plate' => $booking->plate,
                'reason' => $reason,
                'arrears_amount' => $arrears,
            ]);
            $entry->owner_id = $booking->slot->garage->owner_id;
            $entry->save();
        });
    }

    /** Batalkan otomatis pengajuan yang lewat batas 1x24 jam. Return jumlah yang dibatalkan. */
    public function expireStale(): int
    {
        return Booking::whereIn('status', ['menunggu_konfirmasi', 'menunggu_bayar'])
            ->where('expires_at', '<', now())
            ->update(['status' => 'batal', 'expires_at' => null]);
    }

    /** Plat dan nomor HP yang pernah masuk catatan khusus milik satu pemilik. */
    public function flagsFor(int $ownerId): array
    {
        $rows = Blacklist::where('owner_id', $ownerId)->get(['plate', 'phone']);

        return [
            'plates' => $rows->pluck('plate')->all(),
            'phones' => $rows->pluck('phone')->filter()->all(),
        ];
    }

    private function must(Booking $booking, string $status): void
    {
        abort_unless($booking->status === $status, 409, 'Status pengajuan sudah berubah.');
    }
}
