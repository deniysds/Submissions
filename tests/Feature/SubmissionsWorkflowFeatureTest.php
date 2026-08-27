<?php

namespace Modules\Submissions\Tests\Feature;

use App\Support\Editorial\SubmissionWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Models\SubmissionAuthor;
use Modules\Submissions\Models\SubmissionFile;
use Modules\Submissions\Models\SubmissionHistory;
use Modules\Submissions\Services\SubmissionService;
use Modules\Usermanagement\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubmissionsWorkflowFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected User $editor;
    protected Journal $journal;
    protected SubmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->author = User::create([
            'name'     => 'Dr. Author Test',
            'email'    => 'author@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->editor = User::create([
            'name'     => 'Prof. Editor Test',
            'email'    => 'editor@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->journal = Journal::create([
            'name'       => 'Jurnal Bioteknologi Medis',
            'slug'       => 'jurnal-bioteknologi-medis',
            'issn_p'     => '1234-5678',
            'issn_e'     => '8765-4321',
            'is_active'  => true,
        ]);

        $this->service = app(SubmissionService::class);
    }

    #[Test]
    public function author_can_create_draft_submission()
    {
        $submission = $this->service->createDraft($this->author, $this->journal->id);

        $this->assertInstanceOf(Submission::class, $submission);
        $this->assertSame('draft', $submission->status);
        $this->assertSame($this->journal->id, $submission->journal_id);
        $this->assertSame($this->author->id, $submission->corresponding_author_id);
        $this->assertCount(1, $submission->authors);
        $this->assertTrue($submission->authors->first()->is_corresponding_author);
    }

    #[Test]
    public function author_can_upload_and_delete_files()
    {
        $submission = $this->service->createDraft($this->author, $this->journal->id);

        $file = UploadedFile::fake()->create('manuscript.pdf', 1024, 'application/pdf');
        $uploaded = $this->service->uploadFile($submission, $file, 'naskah_utama', $this->author);

        $this->assertInstanceOf(SubmissionFile::class, $uploaded);
        $this->assertSame('naskah_utama', $uploaded->file_role);
        $this->assertDatabaseHas('submission_files', [
            'id'            => $uploaded->id,
            'submission_id' => $submission->id,
            'file_role'     => 'naskah_utama',
        ]);

        $deleted = $this->service->deleteFile($submission, $uploaded->id);
        $this->assertTrue($deleted);
        $this->assertSoftDeleted('submission_files', ['id' => $uploaded->id]);
    }

    #[Test]
    public function author_can_manage_co_authors()
    {
        $submission = $this->service->createDraft($this->author, $this->journal->id);

        $coAuthor = $this->service->addAuthor($submission, [
            'name'        => 'Co-Author Name',
            'email'       => 'coauthor@example.com',
            'affiliation' => 'Universitas Indonesia',
        ], $this->author);

        $this->assertInstanceOf(SubmissionAuthor::class, $coAuthor);
        $this->assertFalse($coAuthor->is_corresponding_author);
        $this->assertDatabaseHas('submission_authors', [
            'id'    => $coAuthor->id,
            'email' => 'coauthor@example.com',
        ]);

        $this->service->deleteAuthor($submission, $coAuthor->id);
        $this->assertSoftDeleted('submission_authors', ['id' => $coAuthor->id]);
    }

    #[Test]
    public function cannot_delete_corresponding_author()
    {
        $submission = $this->service->createDraft($this->author, $this->journal->id);
        $primaryAuthor = $submission->authors()->where('is_corresponding_author', true)->first();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->deleteAuthor($submission, $primaryAuthor->id);
    }

    #[Test]
    public function full_submission_and_editorial_lifecycle_with_history_tracking()
    {
        // 1. Create Draft
        $submission = $this->service->createDraft($this->author, $this->journal->id);

        // 2. Upload Main Manuscript
        $file = UploadedFile::fake()->create('manuscript.docx', 2048, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->service->uploadFile($submission, $file, 'naskah_utama', $this->author);

        // 3. Update Metadata
        $this->service->updateMetadata($submission, [
            'title'    => 'Pengembangan Terapi Genetik untuk Penyakit Menular di Indonesia',
            'abstract' => 'Ini adalah abstrak komprehensif penelitian terapi genetik yang memenuhi standar minimal 50 karakter panjangnya.',
            'keywords' => 'genetika, bioteknologi, terapi',
        ], $this->author);

        // 4. Submit Final (draft -> submitted)
        $submitted = $this->service->submitFinal($submission, $this->author);
        $this->assertSame('submitted', $submitted->status);
        $this->assertNotNull($submitted->submitted_at);

        // Verify history logged for initial submission
        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $submission->id,
            'from_status'   => 'draft',
            'to_status'     => 'submitted',
        ]);

        // 5. Editor transitions to 'under_editor_review'
        $this->service->transitionStatus($submitted, 'under_editor_review', 'Naskah masuk antrean review editor.', $this->editor);
        $this->assertSame('under_editor_review', $submitted->fresh()->status);

        // 6. Editor transitions to 'under_review' (Peer review)
        $this->service->transitionStatus($submitted, 'under_review', 'Naskah dikirimkan ke mitra bestari.', $this->editor);
        $this->assertSame('under_review', $submitted->fresh()->status);

        // 7. Reviewer requests revisions -> 'revision_required'
        $this->service->transitionStatus($submitted, 'revision_required', 'Mohon perbaiki metodologi di Bab 3.', $this->editor);
        $this->assertSame('revision_required', $submitted->fresh()->status);

        // 8. Author resubmits -> 'resubmitted'
        $this->service->transitionStatus($submitted, 'resubmitted', 'Revisi telah diunggah sesuai masukan reviewer.', $this->author);
        $this->assertSame('resubmitted', $submitted->fresh()->status);

        // 9. Editor reviews resubmission -> 'under_editor_review'
        $this->service->transitionStatus($submitted, 'under_editor_review', 'Editor memeriksa revisi naskah.', $this->editor);

        // 10. Editor accepts -> 'accepted'
        $this->service->transitionStatus($submitted, 'accepted', 'Naskah resmi diterima untuk penerbitan.', $this->editor);
        $this->assertSame('accepted', $submitted->fresh()->status);
        $this->assertNotNull($submitted->fresh()->decision_at);

        // 11. Final publish -> 'published'
        $this->service->transitionStatus($submitted, 'published', 'Naskah terbit pada Volume 1 Issue 1.', $this->editor);
        $this->assertSame('published', $submitted->fresh()->status);
        $this->assertTrue($submitted->fresh()->isFinalStatus());

        // Verify all 8 history entries exist in order
        $histories = SubmissionHistory::where('submission_id', $submission->id)->orderBy('happened_at', 'asc')->get();
        $this->assertCount(8, $histories);
    }

    #[Test]
    public function invalid_status_transition_is_blocked()
    {
        $submission = $this->service->createDraft($this->author, $this->journal->id);

        $this->expectException(\InvalidArgumentException::class);
        // Direct transition from draft to published is illegal
        $this->service->transitionStatus($submission, 'published', 'Invalid jump', $this->editor);
    }
}
