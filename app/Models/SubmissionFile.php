<?php

namespace Modules\Submissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'submission_id',
        'file_role',
        'disk',
        'path',
        'original_name',
        'uploaded_by',
        'created_by',
        'updated_by',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
