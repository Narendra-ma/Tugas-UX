<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'note', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function garage(): BelongsTo
    {
        return $this->belongsTo(Garage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeFreeBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->where('is_active', true)
            ->whereDoesntHave('bookings', fn ($b) => $b->blocking()->overlapping($start, $end));
    }

    /**
     * Status tampilan untuk penyewa. Pakai relasi bookings yang sudah di-eager load
     * (sudah difilter blocking + overlapping) supaya tidak N+1.
     */
    public function displayStatus(): string
    {
        if (! $this->is_active) {
            return 'nonaktif';
        }
        if ($this->bookings->contains('status', 'dibayar')) {
            return 'terisi';
        }

        return $this->bookings->isNotEmpty() ? 'menunggu' : 'tersedia';
    }
}
