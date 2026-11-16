<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\StudioPostEditor;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class StudioAuthPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function protectedStudioRoutes(): array
    {
        return [
            'admin index' => ['GET', '/admin'],
            'studio index' => ['GET', '/studio'],
            'studio create' => ['GET', '/studio/create'],
            'studio store' => ['POST', '/studio'],
            'studio edit' => ['GET', '/studio/{post}/edit'],
            'studio update' => ['PUT', '/studio/{post}'],
            'studio destroy' => ['DELETE', '/studio/{post}'],
        ];
    }

    #[DataProvider('protectedStudioRoutes')]
    public function test_guests_are_redirected_to_login_for_admin_and_studio_routes(string $method, string $uri): void
    {
        $post = Post::query()->firstOrFail();
        $resolvedUri = str_replace('{post}', (string) $post->id, $uri);

        $response = $this->call($method, $resolvedUri);

        $response->assertRedirect(route('login'));
    }

    #[DataProvider('protectedStudioRoutes')]
    public function test_authenticated_users_without_roles_or_permissions_receive_forbidden(string $method, string $uri): void
    {
        $user = User::factory()->create();
        $post = Post::query()->firstOrFail();
        $resolvedUri = str_replace('{post}', (string) $post->id, $uri);

        $response = $this->actingAs($user)->call($method, $resolvedUri);

        $response->assertForbidden();
    }

    public function test_editor_can_access_studio_and_manage_posts_but_cannot_delete_posts(): void
    {
        $editor = User::query()->where('email', 'editor@gtavi.pro')->firstOrFail();
        $category = Category::query()->firstOrFail();

        $this->actingAs($editor)->get('/admin')->assertOk()->assertSee('Vice Telemetry Editor');
        $this->actingAs($editor)->get('/studio')->assertOk()->assertDontSee('Delete this dispatch permanently?');
        $this->actingAs($editor)->get('/studio/create')->assertOk();

        $storeResponse = $this->actingAs($editor)->post('/studio', [
            'category_id' => $category->id,
            'title' => 'Editor Authorized Telemetry Dispatch',
            'excerpt' => 'Analyzing editor role permissions and publishing workflow inside the GTAVI.PRO Editorial Studio.',
            'status' => 'draft',
            'verification_badge' => 'technical_projection',
            'schema_type' => 'TechArticle',
            'content_blocks' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Editor dispatch body verifying create and edit permissions.',
                ],
            ],
            'tags' => ['telemetry'],
        ]);

        $created = Post::query()->where('title', 'Editor Authorized Telemetry Dispatch')->firstOrFail();
        $storeResponse->assertRedirect(route('studio.edit', $created));

        $this->actingAs($editor)->get('/studio/'.$created->id.'/edit')->assertOk();

        $updateResponse = $this->actingAs($editor)->put('/studio/'.$created->id, [
            'category_id' => $category->id,
            'title' => 'Editor Updated Telemetry Dispatch',
            'excerpt' => 'Updated dispatch excerpt confirming edit permissions for the editor role in Studio.',
            'status' => 'published',
            'verification_badge' => 'confirmed',
            'schema_type' => 'TechArticle',
            'content_blocks' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Updated editor dispatch body.',
                ],
            ],
            'tags' => ['telemetry'],
        ]);

        $updateResponse->assertRedirect(route('studio.edit', $created));
        $this->assertSame('Editor Updated Telemetry Dispatch', $created->fresh()?->title);

        $this->actingAs($editor)
            ->delete('/studio/'.$created->id)
            ->assertForbidden();

        $this->assertModelExists($created);
    }

    public function test_admin_can_access_all_studio_endpoints_including_delete(): void
    {
        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();
        $post = Post::query()->firstOrFail();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Vice Editorial Admin');
        $this->actingAs($admin)->get('/studio')->assertOk()->assertSee('Delete this dispatch permanently?');

        $deleteResponse = $this->actingAs($admin)->delete('/studio/'.$post->id);

        $deleteResponse->assertRedirect(route('studio.index'));
        $this->assertModelMissing($post);
    }

    public function test_login_page_renders_and_authenticates_valid_credentials(): void
    {
        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();

        $this->get('/login')
            ->assertOk()
            ->assertSee('Editorial Studio Sign In')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('studio.index'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_rejects_invalid_credentials_and_logout_ends_session(): void
    {
        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();

        $failedResponse = $this->from('/login')->post('/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $failedResponse->assertRedirect('/login');
        $failedResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        $logoutResponse = $this->actingAs($admin)->post('/logout');

        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_studio_post_editor_livewire_forbids_unauthenticated_and_unauthorized_users(): void
    {
        $post = Post::query()->firstOrFail();

        Livewire::test(StudioPostEditor::class)->assertForbidden();
        Livewire::test(StudioPostEditor::class, ['post' => $post])->assertForbidden();

        $plainUser = User::factory()->create();

        Livewire::actingAs($plainUser)->test(StudioPostEditor::class)->assertForbidden();
        Livewire::actingAs($plainUser)->test(StudioPostEditor::class, ['post' => $post])->assertForbidden();

        $accessOnlyUser = User::factory()->create();
        $accessOnlyUser->givePermissionTo('access studio');

        Livewire::actingAs($accessOnlyUser)->test(StudioPostEditor::class)->assertForbidden();
        Livewire::actingAs($accessOnlyUser)->test(StudioPostEditor::class, ['post' => $post])->assertForbidden();
    }

    public function test_studio_post_editor_livewire_forbids_save_when_permission_is_revoked(): void
    {
        $editor = User::factory()->editor()->create();
        $post = Post::query()->firstOrFail();

        $component = Livewire::actingAs($editor)->test(StudioPostEditor::class, ['post' => $post]);

        $editor->syncRoles([]);
        $editor->syncPermissions([]);

        $component->call('savePost', 'draft')->assertForbidden();
    }

    public function test_studio_post_editor_livewire_forbids_restore_revision_when_permission_is_revoked(): void
    {
        $editor = User::factory()->editor()->create();
        $post = Post::query()->firstOrFail();
        $revision = PostRevision::query()->create([
            'post_id' => $post->id,
            'title' => $post->title,
            'content_blocks' => $post->content_blocks,
            'seo_score' => $post->seo_score,
            'editor_note' => 'Permission revocation test snapshot',
            'created_at' => now(),
        ]);

        $component = Livewire::actingAs($editor)->test(StudioPostEditor::class, ['post' => $post]);

        $editor->syncRoles([]);
        $editor->syncPermissions([]);

        $component->call('restoreRevision', $revision->id)->assertForbidden();
    }

    public function test_make_super_admin_command_creates_global_super_admin_with_unrestricted_access(): void
    {
        $this->artisan('gta:make-super-admin', [
            '--name' => 'Joe Super Owner',
            '--email' => 'JoeSuper@GTAVI.PRO',
            '--password' => 'UltraStrongPass!2026',
        ])
            ->expectsOutputToContain('Global Super Admin [joesuper@gtavi.pro] is ready')
            ->assertExitCode(Command::SUCCESS);

        $superAdmin = User::query()->where('email', 'joesuper@gtavi.pro')->firstOrFail();

        $this->assertSame('Joe Super Owner', $superAdmin->name);
        $this->assertTrue(Hash::check('UltraStrongPass!2026', $superAdmin->password));
        $this->assertTrue($superAdmin->hasRole('super-admin'));
        $this->assertTrue($superAdmin->hasRole('admin'));
        $this->assertTrue(Gate::forUser($superAdmin)->allows('any-unlisted-global-capability'));
        $this->assertTrue($superAdmin->can('access studio'));
        $this->assertTrue($superAdmin->can('create posts'));
        $this->assertTrue($superAdmin->can('edit posts'));
        $this->assertTrue($superAdmin->can('delete posts'));

        $this->actingAs($superAdmin)->get('/admin')->assertOk()->assertSee('Gemini AI Post Creator');
        $this->actingAs($superAdmin)->get('/studio')->assertOk();
        $this->actingAs($superAdmin)->get('/studio/create')->assertOk();

        $post = Post::query()->firstOrFail();
        $this->actingAs($superAdmin)->delete('/studio/'.$post->id)->assertRedirect(route('studio.index'));
        $this->assertModelMissing($post);
    }

    public function test_make_super_admin_command_promotes_existing_user_and_generates_password_when_omitted(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'promote-me@gtavi.pro',
        ]);
        $this->assertFalse($existingUser->hasRole('super-admin'));

        $this->artisan('gta:make-super-admin', [
            '--email' => 'promote-me@gtavi.pro',
            '--no-interaction' => true,
        ])
            ->expectsOutputToContain('Generated Password')
            ->assertExitCode(Command::SUCCESS);

        $promoted = $existingUser->fresh();
        $this->assertNotNull($promoted);
        $this->assertTrue($promoted->hasRole('super-admin'));
        $this->assertTrue(Gate::forUser($promoted)->allows('manage-global-platform'));

        $this->artisan('gta:make-super-admin', [
            '--email' => 'not-an-email',
        ])->assertExitCode(Command::INVALID);
    }
}
