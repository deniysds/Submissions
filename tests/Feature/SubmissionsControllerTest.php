<?php

namespace Modules\Submissions\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Journals\Models\Journal;
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
        $response = $this->actingAs($this->user)->get(route('submissions.create'));
        $response->assertStatus(200);
        $response->assertViewIs('submissions::create');
        $response->assertSee($this->journal->name);
    }

    public function test_user_can_submit_step1_form()
    {
        $response = $this->actingAs($this->user)->post(route('submissions.store'), [
            'journal_id' => $this->journal->id,
            'checklist'  => ['original', 'template', 'ethics'],
        ]);

        $response->assertRedirect(route('submissions.index'));
        $response->assertSessionHas('success');
    }
}
