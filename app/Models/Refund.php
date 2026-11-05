<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'tour_member_id',
        'total_paid',
        'final_share',
        'adjustment_total',
        'refund_amount',
        'method',
        'refund_date',
        'notes',
        'refunded_by',
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

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function tourMember(): BelongsTo
    {
        return $this->belongsTo(TourMember::class, 'tour_member_id');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
