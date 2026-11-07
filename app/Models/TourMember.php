<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TourMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'user_id',
        'name',
        'phone',
        'email',
        'tour_role',
        'status',
        'joined_at',
    ];

    protected $appends = [
        'display_name',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->user && ! empty($this->user->full_name)) {
            return $this->user->full_name;
        }

        return $this->name ?? 'Member #'.$this->id;
    }

    public function getDisplayPhoneAttribute(): ?string
    {
        return $this->user?->phone ?? $this->phone;
    }

    public function getDisplayEmailAttribute(): ?string
    {
        return $this->user?->email ?? $this->email;
    }

    /**
     * Total amount actually paid by this member across all tour expense shares and advance payments.
     */
    public function totalPaidAmount(): float
    {
        return (float) Payment::where(function ($q) {
            $q->where('tour_member_id', $this->id)
                ->orWhereHas('share', function ($sq) {
                    $sq->where('tour_member_id', $this->id);
                });
        })->where('status', 'verified')->sum('amount');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tour_member_id');
    }

    /**
     * Total expense share assigned to this member.
     */
    public function totalShareAmount(): float
    {
        return (float) $this->expenseShares()->sum('share_amount');
    }

    /**
     * Net balance = Total Amount Paid − Equal Share of Actual Expenses.
     * Positive = Refund due to member
     * Zero = Fully settled
     * Negative = Member still owes money (due)
     */
    public function netBalance(): float
    {
        return round($this->totalPaidAmount() - $this->totalShareAmount(), 2);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function expenseShares(): HasMany
    {
        return $this->hasMany(ExpenseShare::class, 'tour_member_id');
    }

    public function refundAdjustments(): HasMany
    {
        return $this->hasMany(RefundAdjustment::class, 'tour_member_id');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class, 'tour_member_id');
    }
}
