<?php

namespace Modules\Submissions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewRound extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'review_rounds';

    protected $fillable = [
        'submission_id',
        'round_number',
        'status',
        'notes',
        'started_at',
        'closed_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'started_at'   => 'datetime',
            'closed_at'    => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ReviewerAssignment::class, 'review_round_id');
    }
}
