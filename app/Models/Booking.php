<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'menunggu_konfirmasi' => 'Menunggu konfirmasi pemilik',
        'menunggu_bayar' => 'Menunggu pembayaran',
        'dibayar' => 'Dibayar / aktif',
        'selesai' => 'Selesai',
        'batal' => 'Dibatalkan',
        'ditolak' => 'Ditolak',
        'diputus' => 'Diputus sepihak',
    ];

    /** Status yang masih memegang slot. */
    public const BLOCKING = ['menunggu_konfirmasi', 'menunggu_bayar', 'dibayar'];

    public const PERIODS = ['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'];

    protected $fillable = [
        'slot_id', 'car_type', 'plate', 'period', 'start_date', 'end_date',
        'total_price', 'status', 'expires_at', 'agreed_at',
        'payment_proof_path', 'paid_at', 'reject_reason', 'terminate_reason', 'arrears_amount',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'agreed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', self::BLOCKING);
    }

    /** Dua rentang tanggal bentrok kalau saling tumpang tindih (batas hari ikut dihitung). */
    public function scopeOverlapping(Builder $query, string $start, string $end): Builder
    {
        return $query->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start);
    }

    public function scopeForOwner(Builder $query, int $ownerId): Builder
    {
        return $query->whereHas('slot.garage', fn ($g) => $g->where('owner_id', $ownerId));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'dibayar');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->active()->whereDate('end_date', '<', today());
    }

    public function scopeDueSoon(Builder $query, int $days = 3): Builder
    {
        return $query->active()->whereBetween('end_date', [today(), today()->addDays($days)]);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
