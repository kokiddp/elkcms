<?php

namespace Tests\Feature\Admin;

use App\CMS\ContentModels\TestPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'editor']);

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_access_content_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.content.index', ['modelType' => 'test-post']));

        $response->assertStatus(200);
        $response->assertSee('Test Posts');
    }

    public function test_guest_cannot_access_content_index(): void
    {
        $response = $this->get(route('admin.content.index', ['modelType' => 'test-post']));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_access_content_create_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.content.create', ['modelType' => 'test-post']));

        $response->assertStatus(200);
        $response->assertSee('Create Test Posts');
        $response->assertSee('Post Title');
        $response->assertSee('Post Content');
    }

    public function test_admin_can_create_content(): void
    {
        $data = [
            'title' => 'Test Blog Post',
            'content' => 'This is the content of the blog post.',
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.content.store', ['modelType' => 'test-post']), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('test_posts', [
            'title' => 'Test Blog Post',
            'content' => 'This is the content of the blog post.',
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_access_content_edit_form(): void
    {
        $post = TestPost::create([
            'title' => 'Existing Post',
            'content' => 'Existing content',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.content.edit', ['modelType' => 'test-post', 'id' => $post->id]));

        $response->assertStatus(200);
        $response->assertSee('Edit: Existing Post', false);
        $response->assertSee('Existing content', false);
    }

    public function test_admin_can_update_content(): void
    {
        $post = TestPost::create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'status' => 'draft',
        ]);

        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.content.update', ['modelType' => 'test-post', 'id' => $post->id]), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('test_posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_delete_content(): void
    {
        $post = TestPost::create([
            'title' => 'Post to Delete',
            'content' => 'This will be deleted',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.content.destroy', ['modelType' => 'test-post', 'id' => $post->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('test_posts', [
            'id' => $post->id,
        ]);
    }

    public function test_content_validation_requires_title(): void
    {
        $data = [
            'content' => 'Content without title',
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.content.store', ['modelType' => 'test-post']), $data);

        $response->assertSessionHasErrors('title');
    }

    public function test_content_index_shows_pagination(): void
    {
        // Create 25 posts (more than the 20 per page limit)
        for ($i = 1; $i <= 25; $i++) {
            TestPost::create([
                'title' => "Post {$i}",
                'content' => "Content {$i}",
                'status' => 'published',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.content.index', ['modelType' => 'test-post']));

        $response->assertStatus(200);
        $response->assertSee('Post 25', false); // Most recent post (first on page 1)
        $response->assertSee('Post 6', false);  // 20th post (last on page 1)
        $response->assertDontSee('Post 5', false); // Should be on page 2
    }

    public function test_content_index_shows_status_badges(): void
    {
        TestPost::create([
            'title' => 'Published Post',
            'content' => 'Content',
            'status' => 'published',
        ]);

        TestPost::create([
            'title' => 'Draft Post',
            'content' => 'Content',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.content.index', ['modelType' => 'test-post']));

        $response->assertStatus(200);
        $response->assertSee('Published');
        $response->assertSee('Draft');
    }

    public function test_invalid_model_type_returns_404(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.content.index', ['modelType' => 'non-existent-model']));

        $response->assertStatus(404);
    }

    public function test_non_admin_user_cannot_access_content_management(): void
    {
        $user = User::factory()->create();
        // User has no roles

        $response = $this->actingAs($user)
            ->get(route('admin.content.index', ['modelType' => 'test-post']));

        $response->assertStatus(403);
    }
}
