<?php

namespace Modules\Submissions\Models;

use App\Support\Editorial\SubmissionWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Journals\Models\Journal;
use Modules\Usermanagement\Models\User;

class Submission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'journal_id',
        'title',
        'title_en',
        'slug',
        'abstract',
        'abstract_en',
        'keywords',
        'keywords_en',
        'status',
        'submitted_at',
        'decision_at',
        'assigned_editor_id',
        'corresponding_author_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'keywords_en' => 'array',
        'submitted_at' => 'datetime',
        'decision_at' => 'datetime',
    ];

    /**
     * Localized title according to active application locale.
     */
    public function getTitleLocalizedAttribute(): string
    {
        return (app()->getLocale() === 'en' && !empty($this->title_en))
            ? $this->title_en
            : (string) $this->title;
    }

    /**
     * Localized abstract according to active application locale.
     */
    public function getAbstractLocalizedAttribute(): ?string
    {
        return (app()->getLocale() === 'en' && !empty($this->abstract_en))
            ? $this->abstract_en
            : $this->abstract;
    }

    /**
     * Localized keywords according to active application locale.
     */
    public function getKeywordsLocalizedAttribute(): ?array
    {
        return (app()->getLocale() === 'en' && !empty($this->keywords_en))
            ? $this->keywords_en
            : $this->keywords;
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'corresponding_author_id');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'assigned_editor_id');
    }

    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function authors()
    {
        return $this->hasMany(SubmissionAuthor::class)->orderBy('sort_order', 'asc');
    }

    public function histories()
    {
        return $this->hasMany(SubmissionHistory::class)->latest('happened_at');
    }

    public function article(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\Modules\Issues\Models\Article::class, 'submission_id');
    }

    public function reviewRounds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReviewRound::class, 'submission_id')->orderBy('round_number', 'asc');
    }

    public function currentReviewRound(): ?ReviewRound
    {
        return $this->reviewRounds()->latest('round_number')->first();
    }

    /**
     * Check if submission can transition to target status.
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        /** @var SubmissionWorkflow $workflow */
        $workflow = app(SubmissionWorkflow::class);
        return $workflow->canTransition($this->status ?? 'draft', $targetStatus);
    }

    /**
     * Get allowed next statuses.
     */
    public function allowedTransitions(): array
    {
        /** @var SubmissionWorkflow $workflow */
        $workflow = app(SubmissionWorkflow::class);
        return $workflow->allowedTransitions($this->status ?? 'draft');
    }

    /**
     * Check if submission reached a final terminal status.
     */
    public function isFinalStatus(): bool
    {
        /** @var SubmissionWorkflow $workflow */
        $workflow = app(SubmissionWorkflow::class);
        return in_array($this->status, $workflow->finalStatuses(), true);
    }
}
