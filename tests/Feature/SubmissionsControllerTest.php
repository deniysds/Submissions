<?php

namespace Modules\Submissions\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Models\Submission;
use Modules\Submissions\Models\SubmissionAuthor;
use Modules\Submissions\Models\SubmissionFile;
use Modules\Usermanagement\Models\User;
use Tests\TestCase;

class SubmissionsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Journal $journal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('module:migrate', ['module' => 'Usermanagement']);
        $this->artisan('module:migrate', ['module' => 'Journals']);
        $this->artisan('module:migrate', ['module' => 'Submissions']);

        $this->user = User::factory()->create();
        $this->journal = Journal::create([
            'name'        => 'Jurnal Biomedis & Kesehatan',
            'slug'        => 'jbk',
            'short_name'  => 'JBK',
            'issn_p'      => '1234-5678',
            'issn_e'      => '8765-4321',
            'is_active'   => true,
            'description' => 'Deskripsi Jurnal Biomedis.',
        ]);
    }

    public function test_authenticated_user_can_access_submissions_index()
    {
        $response = $this->actingAs($this->user)->get(route('submissions.index'));
        $response->assertStatus(200);
        $response->assertViewIs('submissions::index');
    }

    public function test_authenticated_user_can_access_submission_create_step1()
    {
        $response = $this->actingAs($this->user)->get(route('submissions.create.step1'));
        $response->assertStatus(200);
        $response->assertViewIs('submissions::create');
        $response->assertSee($this->journal->name);
    }

    public function test_user_can_submit_step1_form_to_create_draft()
    {
        $response = $this->actingAs($this->user)->post(route('submissions.store.step1'), [
            'journal_id' => $this->journal->id,
            'checklist'  => ['original', 'template', 'ethics'],
        ]);

        $submission = Submission::first();
        $this->assertNotNull($submission);
        $this->assertEquals('draft', $submission->status);

        $response->assertRedirect(route('submissions.create.step2', $submission->id));
    }

    public function test_user_can_upload_file_in_step2()
    {
        Storage::fake('public');

        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Draf Naskah',
            'slug'                    => 'draft-slug',
            'status'                  => 'draft',
            'corresponding_author_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->create('naskah.docx', 100);

        $response = $this->actingAs($this->user)->post(route('submissions.store.step2', $submission->id), [
            'file'      => $file,
            'file_role' => 'naskah_utama',
        ]);

        $response->assertRedirect(route('submissions.create.step2', $submission->id));
        $this->assertTrue(SubmissionFile::where('submission_id', $submission->id)->exists());
    }

    public function test_user_cannot_continue_step2_without_main_file()
    {
        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Draf Naskah',
            'slug'                    => 'draft-slug',
            'status'                  => 'draft',
            'corresponding_author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('submissions.store.step2', $submission->id), []);

        $response->assertRedirect(route('submissions.create.step2', $submission->id));
        $response->assertSessionHasErrors(['file']);
    }

    public function test_user_can_access_step3_and_update_metadata()
    {
        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Draf Naskah Awal',
            'slug'                    => 'draft-slug',
            'status'                  => 'draft',
            'corresponding_author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('submissions.create.step3', $submission->id));
        $response->assertStatus(200);
        $response->assertViewIs('submissions::step3');

        $postResponse = $this->actingAs($this->user)->post(route('submissions.store.step3', $submission->id), [
            'title'    => 'Pengaruh Genomics Terhadap Kesehatan Masyarakat',
            'abstract' => 'Abstrak penelitian ini membahas pengaruh teknologi genomik modern secara mendalam untuk masyarakat.',
            'keywords' => 'Genomik, Kesehatan, Biomolekuler',
        ]);

        $postResponse->assertRedirect(route('submissions.create.step4', $submission->id));
        $this->assertDatabaseHas('submissions', [
            'id'    => $submission->id,
            'title' => 'Pengaruh Genomics Terhadap Kesehatan Masyarakat',
        ]);
    }

    public function test_user_can_add_and_delete_co_authors()
    {
        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Draf Naskah',
            'slug'                    => 'draft-slug',
            'status'                  => 'draft',
            'corresponding_author_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('submissions.add_author.step3', $submission->id), [
            'name'        => 'Dr. Budi Santoso',
            'email'       => 'budi@satriabudi.org',
            'affiliation' => 'Universitas Indonesia',
        ]);

        $response->assertRedirect(route('submissions.create.step3', $submission->id));
        
        $author = SubmissionAuthor::where('email', 'budi@satriabudi.org')->first();
        $this->assertNotNull($author);

        $deleteResponse = $this->actingAs($this->user)->delete(route('submissions.delete_author.step3', [$submission->id, $author->id]));
        $deleteResponse->assertRedirect(route('submissions.create.step3', $submission->id));
        $this->assertSoftDeleted($author);
    }

    public function test_user_can_finish_step4_and_submit_manuscript()
    {
        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Analisis Genomik Terapan',
            'slug'                    => 'analisis-genomik',
            'abstract'                => 'Ini adalah ringkasan abstrak naskah ilmiah terapan genomik berkualitas tinggi.',
            'keywords'                => ['Genomik', 'Analisis'],
            'status'                  => 'draft',
            'corresponding_author_id' => $this->user->id,
        ]);

        SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_role'     => 'naskah_utama',
            'disk'          => 'public',
            'path'          => 'submissions/1/naskah.docx',
            'original_name' => 'naskah.docx',
            'uploaded_by'   => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('submissions.create.step4', $submission->id));
        $response->assertStatus(200);
        $response->assertViewIs('submissions::step4');

        $finishResponse = $this->actingAs($this->user)->post(route('submissions.finish.step4', $submission->id));
        $finishResponse->assertRedirect(route('submissions.index'));

        $this->assertDatabaseHas('submissions', [
            'id'     => $submission->id,
            'status' => 'submitted',
        ]);
    }

    public function test_editor_can_view_submitted_manuscripts_and_make_decision()
    {
        $submission = Submission::create([
            'journal_id'              => $this->journal->id,
            'title'                   => 'Riset Genomik Manusia',
            'slug'                    => 'riset-genomik',
            'abstract'                => 'Abstrak riset genomik manusia.',
            'status'                  => 'under_editor_review',
            'corresponding_author_id' => $this->user->id,
        ]);

        $indexResponse = $this->actingAs($this->user)->get(route('editor.submissions.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertViewIs('submissions::editor.index');
        $indexResponse->assertSee($submission->title);

        $showResponse = $this->actingAs($this->user)->get(route('editor.submissions.show', $submission->id));
        $showResponse->assertStatus(200);
        $showResponse->assertViewIs('submissions::editor.show');

        $decisionResponse = $this->actingAs($this->user)->post(route('editor.submissions.decision', $submission->id), [
            'status' => 'accepted',
            'notes'  => 'Naskah memenuhi standar publikasi tinggi.',
        ]);

        $decisionResponse->assertRedirect(route('editor.submissions.show', $submission->id));
        $this->assertDatabaseHas('submissions', [
            'id'     => $submission->id,
            'status' => 'accepted',
        ]);
    }
}
