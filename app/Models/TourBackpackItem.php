<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourBackpackItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'category',
        'item_name',
        'is_packed',
        'is_custom',
    ];

    protected function casts(): array
    {
        return [
            'is_packed' => 'boolean',
            'is_custom' => 'boolean',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }
}
