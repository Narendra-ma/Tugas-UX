<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    public const CATEGORIES = [
        'perjanjian' => 'Perjanjian sewa',
        'bukti_bayar' => 'Bukti bayar',
        'identitas' => 'Identitas',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = ['category', 'path', 'original_name'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
