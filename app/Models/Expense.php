<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_id',
        'category_id',
        'added_by',
        'expense_name',
        'total_amount',
        'expense_date',
        'description',
        'receipt_file',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class, 'tour_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(ExpenseShare::class, 'expense_id');
    }
}
