<?php

namespace Tests\Feature;

use App\Enums\DocumentVisibility;
use App\Enums\PermissionName;
use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\Document;
use App\Models\LeaderAssignment;
use App\Models\LeadershipTerm;
use App\Models\Ministry;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_leader_without_create_permission_cannot_upload(): void
    {
        $leader = $this->userWithRole('reader');

        $this->actingAs($leader)
            ->get(route('leader.documents.create'))
            ->assertForbidden();
    }

    public function test_a_leader_can_upload_a_document(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $leader = $this->userWithRole('uploader', [
            PermissionName::DocumentCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.documents.store'), [
                'title' => 'DARUSO Constitution',
                'description' => 'The governing constitution.',
                'category' => 'constitution',
                'visibility' => DocumentVisibility::Public->value,
                'file' => UploadedFile::fake()->create('constitution.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'title' => 'DARUSO Constitution',
            'uploader_id' => $leader->getKey(),
            'version' => 1,
        ]);
    }

    public function test_upload_rejects_an_executable_file_type(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $leader = $this->userWithRole('uploader', [
            PermissionName::DocumentCreate->value,
        ]);

        // A PHP script disguised with an allowed extension. `mimes` inspects the
        // file contents rather than trusting the name or the client MIME type,
        // so this is rejected even though the name looks acceptable.
        $script = UploadedFile::fake()->createWithContent('constitution.pdf', '<?php echo "pwned"; ?>');

        $this->actingAs($leader)
            ->post(route('leader.documents.store'), [
                'title' => 'Malicious payload',
                'description' => 'A file that must be rejected.',
                'category' => 'other',
                'visibility' => DocumentVisibility::Public->value,
                'file' => $script,
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_upload_rejects_a_disallowed_extension(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $leader = $this->userWithRole('uploader', [
            PermissionName::DocumentCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.documents.store'), [
                'title' => 'Executable upload',
                'description' => 'Executables must never be accepted.',
                'category' => 'other',
                'visibility' => DocumentVisibility::Public->value,
                'file' => UploadedFile::fake()->create('payload.exe', 10, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('documents', 0);
    }

    public function test_ministry_visibility_requires_the_matching_ministry(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $term = LeadershipTerm::factory()->active()->create();
        $position = Position::factory()->create();
        $ministry = Ministry::factory()->create(['name' => 'Welfare Ministry']);
        $otherMinistry = Ministry::factory()->create(['name' => 'Health Ministry']);

        $leader = $this->userWithRole('uploader', [
            PermissionName::DocumentCreate->value,
            PermissionName::DocumentPublish->value,
        ]);

        LeaderAssignment::create([
            'user_id' => $leader->getKey(),
            'position_id' => $position->getKey(),
            'ministry_id' => $ministry->getKey(),
            'leadership_term_id' => $term->getKey(),
        ]);

        $document = Document::factory()->withStoredFile()->create([
            'visibility' => DocumentVisibility::Ministry->value,
            'ministry_id' => $otherMinistry->getKey(),
            'uploader_id' => $this->userWithRole('other')->getKey(),
        ]);

        // The leader belongs to Welfare, not Health, so the document is out of reach.
        $this->actingAs($leader->fresh())
            ->get(route('leader.documents.show', $document))
            ->assertForbidden();
    }

    public function test_committee_visibility_requires_membership_of_that_committee(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $term = LeadershipTerm::factory()->active()->create();
        $committee = Committee::factory()->create();
        $otherCommittee = Committee::factory()->create();

        $leader = $this->userWithRole('member', [
            PermissionName::DocumentCreate->value,
        ]);

        CommitteeMember::create([
            'user_id' => $leader->getKey(),
            'committee_id' => $committee->getKey(),
            'leadership_term_id' => $term->getKey(),
        ]);

        $document = Document::factory()->withStoredFile()->create([
            'visibility' => DocumentVisibility::Committee->value,
            'committee_id' => $otherCommittee->getKey(),
            'uploader_id' => $leader->getKey(),
        ]);

        $this->actingAs($leader->fresh())
            ->get(route('student.documents.show', $document))
            ->assertForbidden();
    }

    public function test_a_student_sees_public_and_student_documents_only(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $student = $this->student();

        Document::factory()->public()->withStoredFile()->create(['title' => 'Public constitution']);
        Document::factory()->withStoredFile()->create([
            'title' => 'Student policy',
            'visibility' => DocumentVisibility::Students->value,
        ]);
        Document::factory()->withStoredFile()->create([
            'title' => 'Leader minutes',
            'visibility' => DocumentVisibility::Leaders->value,
            'uploader_id' => $this->userWithRole('other')->getKey(),
        ]);

        $this->actingAs($student)
            ->get(route('student.documents.index'))
            ->assertOk()
            ->assertSee('Public constitution')
            ->assertSee('Student policy')
            ->assertDontSee('Leader minutes');
    }

    public function test_a_student_cannot_open_a_leader_only_document(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $student = $this->student();
        $document = Document::factory()->withStoredFile()->create([
            'visibility' => DocumentVisibility::Leaders->value,
            'uploader_id' => $this->userWithRole('other')->getKey(),
        ]);

        $this->actingAs($student)
            ->get(route('student.documents.show', $document))
            ->assertForbidden();
    }

    public function test_an_authorised_download_streams_the_file(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $student = $this->student();
        $document = Document::factory()->public()->withStoredFile('constitution contents')->create();

        $response = $this->actingAs($student)
            ->get(route('student.documents.download', $document));

        $response->assertOk();
        $response->assertDownload($document->file_name);
    }

    public function test_download_is_denied_when_the_policy_denies_view(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $student = $this->student();
        $document = Document::factory()->withStoredFile()->create([
            'visibility' => DocumentVisibility::Leaders->value,
            'uploader_id' => $this->userWithRole('other')->getKey(),
        ]);

        $this->actingAs($student)
            ->get(route('student.documents.download', $document))
            ->assertForbidden();
    }

    public function test_uploading_a_new_file_creates_a_superseding_version(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $leader = $this->userWithRole('uploader', [
            PermissionName::DocumentCreate->value,
            PermissionName::DocumentPublish->value,
        ]);

        $original = Document::factory()->public()->withStoredFile('v1')->create([
            'title' => 'Policy',
            'uploader_id' => $leader->getKey(),
            'version' => 1,
        ]);

        $this->actingAs($leader)
            ->put(route('leader.documents.update', $original), [
                'title' => 'Policy',
                'description' => 'Second edition.',
                'category' => $original->category->value,
                'visibility' => DocumentVisibility::Public->value,
                'file' => UploadedFile::fake()->create('policy-v2.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        // The original row is preserved; the new file becomes version 2.
        $this->assertSame(1, $original->fresh()->version);

        $this->assertDatabaseHas('documents', [
            'supersedes_id' => $original->getKey(),
            'title' => 'Policy',
            'version' => 2,
            'file_name' => 'policy-v2.pdf',
        ]);
    }

    public function test_deleting_a_document_requires_the_delete_permission(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $leader = $this->userWithRole('uploader', [
            PermissionName::DocumentCreate->value,
        ]);

        $document = Document::factory()->public()->withStoredFile()->create([
            'uploader_id' => $leader->getKey(),
        ]);

        $this->actingAs($leader)
            ->delete(route('leader.documents.destroy', $document))
            ->assertForbidden();

        $this->assertDatabaseHas('documents', ['id' => $document->getKey()]);
    }

    public function test_upload_is_audited(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $leader = $this->userWithRole('uploader', [
            PermissionName::DocumentCreate->value,
        ]);

        $this->actingAs($leader)
            ->post(route('leader.documents.store'), [
                'title' => 'Audited upload',
                'description' => 'Should be recorded.',
                'category' => 'policy',
                'visibility' => DocumentVisibility::Public->value,
                'file' => UploadedFile::fake()->create('policy.pdf', 50, 'application/pdf'),
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'document_uploaded',
            'actor_id' => $leader->getKey(),
        ]);
    }

    public function test_group_visibility_scopes_to_the_named_user(): void
    {
        Storage::fake(config('daruso.uploads.disk'));

        $member = $this->student();
        $outsider = $this->student();

        $document = Document::factory()->withStoredFile()->create([
            'visibility' => DocumentVisibility::Group->value,
            'group_id' => $member->getKey(),
            'uploader_id' => $member->getKey(),
        ]);

        $this->actingAs($member->fresh())
            ->get(route('student.documents.show', $document))
            ->assertOk();

        $this->actingAs($outsider)
            ->get(route('student.documents.show', $document))
            ->assertForbidden();
    }
}
