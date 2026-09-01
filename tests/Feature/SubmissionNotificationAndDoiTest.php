<?php

namespace Modules\Submissions\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Mail\SubmissionStatusUpdatedMail;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Services\SubmissionService;
use Modules\Usermanagement\Models\User;
use Tests\TestCase;

class SubmissionNotificationAndDoiTest extends TestCase
{
    use RefreshDatabase;

    protected User $author;
    protected User $editor;
    protected Journal $journal;
    protected SubmissionService $submissionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('module:migrate', ['module' => 'Usermanagement']);
        $this->artisan('module:migrate', ['module' => 'Journals']);
        $this->artisan('module:migrate', ['module' => 'Submissions']);

        $this->author = User::factory()->create(['name' => 'Penulis Riset', 'email' => 'penulis@satriabudi.org']);
        $this->editor = User::factory()->create(['name' => 'Editor Riset', 'email' => 'editor@satriabudi.org']);

        $this->journal = Journal::create([
            'name'        => 'Indonesian Genomic Journal',
            'slug'        => 'igj',
            'short_name'  => 'IGJ',
            'issn_p'      => '1234-5678',
            'issn_e'      => '8765-4321',
            'is_active'   => true,
        ]);

        $this->submissionService = app(SubmissionService::class);
    }

    public function test_email_notification_is_sent_when_editor_changes_status(): void
    {
        Mail::fake();

        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Analisis Bioinformatika Genom',
            'slug'                    => 'analisis-bioinformatika',
            'abstract'                => 'Abstrak riset bioinformatika yang sangat mendalam.',
            'status'                  => 'submitted',
            'corresponding_author_id' => $this->author->id,
            'created_by'              => $this->author->id,
        ]);

        $this->submissionService->transitionStatus(
            $submission,
            'under_editor_review',
            'Naskah sedang diperiksa oleh dewan redaksi pelaksana.',
            $this->editor
        );

        Mail::assertSent(SubmissionStatusUpdatedMail::class, function ($mail) use ($submission) {
            return $mail->hasTo('penulis@satriabudi.org') &&
                   $mail->submission->id === $submission->id &&
                   $mail->toStatus === 'under_editor_review';
        });
    }

    public function test_author_and_editor_can_access_doi_certificate(): void
    {
        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Karakterisasi Varian DNA Populasi',
            'slug'                    => 'karakterisasi-dna',
            'abstract'                => 'Abstrak penelitian DNA terlengkap untuk wilayah Indonesia timur.',
            'keywords'                => ['DNA', 'Genomik', 'Variasi'],
            'status'                  => 'accepted',
            'corresponding_author_id' => $this->author->id,
            'created_by'              => $this->author->id,
        ]);

        // Author access
        $authorResponse = $this->actingAs($this->author)->get(route('submissions.doi_certificate', $submission->id));
        $authorResponse->assertStatus(200);
        $authorResponse->assertViewIs('submissions::doi.certificate');
        $authorResponse->assertSee('Digital Object Identifier');
        $authorResponse->assertSee('10.1234/ignite.igj.' . $submission->id);

        // Editor access
        $editorResponse = $this->actingAs($this->editor)->get(route('editor.submissions.doi_certificate', $submission->id));
        $editorResponse->assertStatus(200);
        $editorResponse->assertViewIs('submissions::doi.certificate');
        $editorResponse->assertSee('Karakterisasi Varian DNA Populasi');
    }
}
