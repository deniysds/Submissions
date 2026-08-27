<?php

namespace Modules\Submissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Usermanagement\Models\User;

class SubmissionHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'submission_id',
        'from_status',
        'to_status',
        'actor_id',
        'notes',
        'happened_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'happened_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
