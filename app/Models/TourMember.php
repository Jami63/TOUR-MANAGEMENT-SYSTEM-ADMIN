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
        'tour_role',
        'status',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
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
