<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_member_id',
        'total_paid',
        'final_share',
        'adjustment_total',
        'refund_amount',
        'method',
        'refund_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_paid' => 'decimal:2',
            'final_share' => 'decimal:2',
            'adjustment_total' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'refund_date' => 'date',
        ];
    }

    public function tourMember(): BelongsTo
    {
        return $this->belongsTo(TourMember::class, 'tour_member_id');
    }
}
