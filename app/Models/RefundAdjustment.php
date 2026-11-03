<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_member_id',
        'created_by',
        'amount',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function tourMember(): BelongsTo
    {
        return $this->belongsTo(TourMember::class, 'tour_member_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
