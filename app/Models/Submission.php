<?php

namespace Modules\Submissions\Models;

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
        'slug',
        'abstract',
        'keywords',
        'status',
        'submitted_at',
        'decision_at',
        'assigned_editor_id',
        'corresponding_author_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'submitted_at' => 'datetime',
        'decision_at' => 'datetime',
    ];

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
        return $this->hasMany(SubmissionAuthor::class);
    }
}
