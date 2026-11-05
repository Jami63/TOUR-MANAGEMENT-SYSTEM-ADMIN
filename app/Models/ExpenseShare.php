<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
        'tour_member_id',
        'share_amount',
    ];

    protected function casts(): array
    {
        return [
            'share_amount' => 'decimal:2',
        ];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function tourMember(): BelongsTo
    {
        return $this->belongsTo(TourMember::class, 'tour_member_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'share_id');
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->where('status', 'verified')->sum('amount');
    }

    public function remainingDue(): float
    {
        return max(0.0, round((float) $this->share_amount - $this->paidAmount(), 2));
    }

    public function paymentStatus(): string
    {
        $paid = $this->paidAmount();
        $share = (float) $this->share_amount;

        if ($paid >= $share && $share > 0) {
            return 'Paid';
        }
        if ($paid > 0) {
            return 'Partial';
        }

        return 'Pending';
    }

    public function getPaidAmountAttribute(): float
    {
        return $this->paidAmount();
    }

    public function getRemainingDueAttribute(): float
    {
        return $this->remainingDue();
    }

    public function getPaymentStatusAttribute(): string
    {
        return $this->paymentStatus();
    }
}
