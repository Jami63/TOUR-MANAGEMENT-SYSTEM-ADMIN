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
}
