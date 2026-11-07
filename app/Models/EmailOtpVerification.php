<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailOtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'payload',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    public function isValidOtp(string $candidate): bool
    {
        return ! $this->isExpired() && hash_equals($this->otp, trim($candidate));
    }
}
