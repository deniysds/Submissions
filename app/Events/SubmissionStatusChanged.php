<?php

namespace Modules\Submissions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Submissions\Models\Submission;
use Modules\Usermanagement\Models\User;

class SubmissionStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Submission $submission,
        public string $fromStatus,
        public string $toStatus,
        public ?User $actor = null,
        public ?string $notes = null
    ) {}
}
