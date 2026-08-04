<?php

namespace Modules\Submissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionAuthor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'submission_id',
        'name',
        'email',
        'affiliation',
        'is_corresponding_author',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_corresponding_author' => 'boolean',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
