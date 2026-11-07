<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'tour_name',
        'start_location',
        'start_date',
        'end_date',
        'estimated_budget',
        'description',
        'other_info',
        'status',
        'finish_type',
        'finished_at',
        'emergency_reason',
        'settlement_notes',
        'total_collected',
        'total_actual_expense',
        'total_refund_amount',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'estimated_budget' => 'decimal:2',
            'finished_at' => 'datetime',
            'total_collected' => 'decimal:2',
            'total_actual_expense' => 'decimal:2',
            'total_refund_amount' => 'decimal:2',
            'is_archived' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(TourDestination::class, 'tour_id')->orderBy('visit_order');
    }

    public function members(): HasMany
    {
        return $this->hasMany(TourMember::class, 'tour_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tour_members', 'tour_id', 'user_id')
            ->withPivot(['id', 'tour_role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'tour_id');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(TourTimeline::class, 'tour_id')->latest();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'tour_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'tour_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tour_id');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'emergency_finished']);
    }

    public function isEmergencyFinished(): bool
    {
        return $this->finish_type === 'emergency' || $this->status === 'emergency_finished';
    }

    public function totalActualExpenses(): float
    {
        return (float) $this->expenses()->sum('total_amount');
    }

    public function totalCollectedMoney(): float
    {
        return (float) Payment::where(function ($q) {
            $q->where('tour_id', $this->id)
                ->orWhereHas('share.expense', function ($sq) {
                    $sq->where('tour_id', $this->id);
                });
        })->where('status', 'verified')->sum('amount');
    }
}
