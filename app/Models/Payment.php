<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'share_id',
        'amount',
        'method',
        'transaction_id',
        'proof_file',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function share(): BelongsTo
    {
        return $this->belongsTo(ExpenseShare::class, 'share_id');
    }

    public function verification(): HasOne
    {
        return $this->hasOne(PaymentVerification::class, 'payment_id');
    }
}
