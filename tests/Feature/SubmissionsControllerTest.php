<?php

namespace Modules\Submissions\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Journals\Models\Journal;
use Modules\Submissions\Models\Submission;
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
}
