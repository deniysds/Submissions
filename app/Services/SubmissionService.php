<?php

namespace Modules\Submissions\Services;

use App\Support\Editorial\SubmissionWorkflow;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Events\SubmissionStatusChanged;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Models\SubmissionAuthor;
use Modules\Submissions\Models\SubmissionFile;
use Modules\Submissions\Models\SubmissionHistory;
use Modules\Usermanagement\Models\User;

class SubmissionService
{
    public function __construct(
        protected SubmissionWorkflow $workflow
    ) {}

    /**
     * Create a new draft submission for an author.
     */
    public function createDraft(User $user, int $journalId): Submission
    {
        return DB::transaction(function () use ($user, $journalId) {
            $submission = Submission::create([
                'journal_id'              => $journalId,
                'title'                   => 'Draf Naskah Tanpa Judul - ' . Str::random(5),
                'slug'                    => 'draft-' . Str::uuid(),
                'status'                  => 'draft',
                'corresponding_author_id' => $user->id,
                'created_by'              => $user->id,
            ]);

            SubmissionAuthor::create([
                'submission_id'           => $submission->id,
                'name'                    => $user->name,
                'email'                   => $user->email,
                'affiliation'             => $user->position?->name ?? 'Yayasan Satriabudi Dharma Setia',
                'is_corresponding_author' => true,
                'sort_order'              => 1,
                'created_by'              => $user->id,
            ]);

            return $submission;
        });
    }

    /**
     * Upload and attach a file to submission.
     */
    public function uploadFile(Submission $submission, UploadedFile $file, string $fileRole, User $user): SubmissionFile
    {
        return DB::transaction(function () use ($submission, $file, $fileRole, $user) {
            $originalName = $file->getClientOriginalName();
            $path = $file->store('submissions/' . $submission->id, 'public');

            return SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_role'     => $fileRole,
                'disk'          => 'public',
                'path'          => $path,
                'original_name' => $originalName,
                'uploaded_by'   => $user->id,
                'created_by'    => $user->id,
            ]);
        });
    }

    /**
     * Delete an attached file.
     */
    public function deleteFile(Submission $submission, int $fileId): bool
    {
        return DB::transaction(function () use ($submission, $fileId) {
            $file = SubmissionFile::where('submission_id', $submission->id)->findOrFail($fileId);

            if (Storage::disk($file->disk)->exists($file->path)) {
                Storage::disk($file->disk)->delete($file->path);
            }

            return (bool) $file->delete();
        });
    }

    /**
     * Add co-author to submission.
     */
    public function addAuthor(Submission $submission, array $data, User $user): SubmissionAuthor
    {
        return DB::transaction(function () use ($submission, $data, $user) {
            $maxOrder = SubmissionAuthor::where('submission_id', $submission->id)->max('sort_order') ?? 0;

            return SubmissionAuthor::create([
                'submission_id'           => $submission->id,
                'name'                    => $data['name'],
                'email'                   => $data['email'],
                'affiliation'             => $data['affiliation'] ?? null,
                'is_corresponding_author' => false,
                'sort_order'              => $maxOrder + 1,
                'created_by'              => $user->id,
            ]);
        });
    }

    /**
     * Delete a co-author (cannot delete corresponding author).
     */
    public function deleteAuthor(Submission $submission, int $authorId): bool
    {
        $author = SubmissionAuthor::where('submission_id', $submission->id)->findOrFail($authorId);

        if ($author->is_corresponding_author) {
            throw new \InvalidArgumentException('Penulis utama (corresponding author) tidak dapat dihapus.');
        }

        return (bool) $author->delete();
    }

    /**
     * Update metadata (title, abstract, keywords).
     */
    public function updateMetadata(Submission $submission, array $data, User $user): Submission
    {
        return DB::transaction(function () use ($submission, $data, $user) {
            $keywords = is_array($data['keywords'])
                ? $data['keywords']
                : array_map('trim', explode(',', (string) $data['keywords']));

            $title = $data['title'];
            $baseSlug = Str::slug(Str::limit($title, 100));
            $slug = $baseSlug . '-' . Str::random(5);

            $submission->update([
                'title'      => $title,
                'slug'       => $slug,
                'abstract'   => $data['abstract'],
                'keywords'   => $keywords,
                'updated_by' => $user->id,
            ]);

            return $submission;
        });
    }

    /**
     * Final submission by author (transition from 'draft' to 'submitted').
     */
    public function submitFinal(Submission $submission, User $user): Submission
    {
        $hasMainFile = SubmissionFile::where('submission_id', $submission->id)
            ->where('file_role', 'naskah_utama')
            ->exists();

        if (!$hasMainFile || Str::startsWith($submission->title, 'Draf Naskah Tanpa Judul') || empty($submission->abstract)) {
            throw new \InvalidArgumentException('Naskah belum memenuhi syarat kelengkapan (Judul, Abstrak, atau Naskah Utama belum terisi).');
        }

        return $this->transitionStatus(
            $submission,
            'submitted',
            'Naskah diserahkan secara final oleh penulis untuk proses peninjauan.',
            $user
        );
    }

    /**
     * Transition submission status using SubmissionWorkflow state machine with history logging.
     */
    public function transitionStatus(Submission $submission, string $targetStatus, ?string $notes = null, ?User $actor = null): Submission
    {
        $currentStatus = $submission->status ?? 'draft';

        if (!$this->workflow->canTransition($currentStatus, $targetStatus)) {
            throw new \InvalidArgumentException(
                "Transisi status dari '{$currentStatus}' ke '{$targetStatus}' tidak diizinkan oleh workflow editorial."
            );
        }

        return DB::transaction(function () use ($submission, $currentStatus, $targetStatus, $notes, $actor) {
            $updates = [
                'status'     => $targetStatus,
                'updated_by' => $actor?->id,
            ];

            if ($targetStatus === 'submitted') {
                $updates['submitted_at'] = now();
            }

            if (in_array($targetStatus, ['accepted', 'rejected', 'published'], true)) {
                $updates['decision_at'] = now();
            }

            if ($actor && $actor->id !== $submission->corresponding_author_id) {
                $updates['assigned_editor_id'] = $actor->id;
            }

            $submission->update($updates);

            SubmissionHistory::create([
                'submission_id' => $submission->id,
                'from_status'   => $currentStatus,
                'to_status'     => $targetStatus,
                'actor_id'      => $actor?->id,
                'notes'         => $notes,
                'happened_at'   => now(),
                'created_by'    => $actor?->id,
            ]);

            event(new SubmissionStatusChanged($submission, $currentStatus, $targetStatus, $actor, $notes));

            return $submission->fresh(['journal', 'files', 'authors', 'histories']);
        });
    }

    /**
     * Assign editor to submission.
     */
    public function assignEditor(Submission $submission, int $editorId, User $actor): Submission
    {
        return DB::transaction(function () use ($submission, $editorId, $actor) {
            $editor = User::findOrFail($editorId);

            $submission->update([
                'assigned_editor_id' => $editor->id,
                'updated_by'         => $actor->id,
            ]);

            SubmissionHistory::create([
                'submission_id' => $submission->id,
                'from_status'   => $submission->status,
                'to_status'     => $submission->status,
                'actor_id'      => $actor->id,
                'notes'         => "Editor ditugaskan: {$editor->name}",
                'happened_at'   => now(),
                'created_by'    => $actor->id,
            ]);

            return $submission;
        });
    }

    /**
     * Query author submissions.
     */
    public function getAuthorSubmissions(User $user, Request $request)
    {
        $query = Submission::with(['journal', 'files', 'authors'])
            ->where('corresponding_author_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->latest('updated_at')->paginate((int) $request->get('per_page', 10));
    }

    /**
     * Query submissions for editor review.
     */
    public function getEditorSubmissions(Request $request)
    {
        $query = Submission::with(['journal', 'author', 'files', 'authors'])
            ->where('status', '!=', 'draft');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('journal_id')) {
            $query->where('journal_id', $request->journal_id);
        }

        return $query->latest('submitted_at')->paginate((int) $request->get('per_page', 10));
    }
}
