<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'city', 'address', 'description', 'size_note',
        'price_daily', 'price_weekly', 'price_monthly', 'photo_path', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(Slot::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Garasi yang masih punya minimal satu slot aktif kosong di rentang tanggal. */
    public function scopeFreeBetween(Builder $query, string $start, string $end): Builder
    {
        return $query->whereHas('slots', fn ($s) => $s->freeBetween($start, $end));
    }

    public function priceFor(string $period): int
    {
        return $this->{'price_'.$period};
    }
}
