<?php

namespace Modules\Submissions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Usermanagement\Models\User;

class ReviewerAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reviewer_assignments';

    protected $fillable = [
        'review_round_id',
        'reviewer_id',
        'invitation_token',
        'response_status',
        'due_date',
        'recommendation',
        'comments_for_author',
        'confidential_comments_to_editor',
        'submitted_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date'     => 'date',
            'submitted_at' => 'datetime',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(ReviewRound::class, 'review_round_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
