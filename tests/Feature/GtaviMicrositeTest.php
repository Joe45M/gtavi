<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\BlogIndexFilter;
use App\Livewire\InteractiveCalculators;
use App\Livewire\StudioPostEditor;
use App\Models\Category;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class GtaviMicrositeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_all_public_microsite_and_blog_routes_render_with_disclaimer_and_json_ld(): void
    {
        $publicRoutes = [
            '/',
            '/release-info',
            '/prices-editions',
            '/download-sizes',
            '/calculators',
            '/map-atlas',
            '/disclaimer',
            '/gta',
            '/gta/gta-vi-download-size-pre-load-math-ssd-guide',
            '/gta/category/release-take-two-filings',
            '/gta/tag/leonida-map',
        ];

        foreach ($publicRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertOk();
            $response->assertSee('Rockstar Games', false);
            $response->assertSee('Take-Two Interactive', false);
            $response->assertSee('<script type="application/ld+json">', false);
        }

        foreach (['/', '/gta', '/gta/gta-vi-download-size-pre-load-math-ssd-guide', '/prices-editions'] as $publicUri) {
            $publicResponse = $this->get($publicUri);
            $publicResponse->assertOk();
            $publicResponse->assertDontSee('bg-gradient-', false);
            $publicResponse->assertDontSee('/100 SEO', false);
            $publicResponse->assertDontSee('Avg Dispatch SEO', false);
        }

        foreach (['/admin', '/studio'] as $guestProtectedUri) {
            $this->get($guestProtectedUri)->assertRedirect(route('login'));
        }

        $admin = User::query()->where('email', 'admin@gtavi.pro')->firstOrFail();
        $firstPost = Post::query()->firstOrFail();
        $studioRoutes = [
            '/admin',
            '/studio',
            '/studio/create',
            '/studio/'.$firstPost->id.'/edit',
        ];

        $this->actingAs($admin);

        foreach ($studioRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertOk();
            $response->assertSee('Rockstar Games', false);
            $response->assertSee('Take-Two Interactive', false);
            $response->assertSee('<script type="application/ld+json">', false);
        }

        foreach (['/studio', '/admin'] as $adminUri) {
            $adminResponse = $this->get($adminUri);
            $adminResponse->assertOk();
            $adminResponse->assertSee('INTERNAL ADMIN VIEW', false);
            $adminResponse->assertSee('/100 SEO', false);
        }
    }

    public function test_seo_machine_endpoints_and_download_post_endpoint_work(): void
    {
        $sitemap = $this->get('/sitemap.xml');
        $sitemap->assertOk();
        $sitemap->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $sitemap->assertSee('<urlset', false);
        $sitemap->assertSee('/gta/gta-vi-download-size-pre-load-math-ssd-guide', false);

        $this->get('/blog')->assertRedirect('/gta')->assertStatus(301);
        $this->get('/blog/gta-vi-download-size-pre-load-math-ssd-guide')
            ->assertRedirect('/gta/gta-vi-download-size-pre-load-math-ssd-guide')
            ->assertStatus(301);

        $robots = $this->get('/robots.txt');
        $robots->assertOk();
        $robots->assertSee('Sitemap:', false);
        $robots->assertSee('Disallow: /studio', false);

        $llms = $this->get('/llms.txt');
        $llms->assertOk();
        $llms->assertSee('GTAVI.PRO', false);
        $llms->assertSee('NOT affiliated with', false);

        $estimate = $this->postJson('/calculators/download-estimate', [
            'speed' => 500,
            'unit' => 'Mbps',
            'size_gb' => 170,
            'overhead_percent' => 10,
            'data_cap_gb' => 1000,
        ]);
        $estimate->assertOk();
        $estimate->assertJsonPath('data.raw_mbps', 500);
        $estimate->assertJsonStructure([
            'data' => [
                'effective_mbps',
                'formatted_duration',
                'recommended_free_ssd_gb',
                'tiers',
            ],
            'disclaimer',
        ]);
    }

    public function test_public_rest_api_v1_endpoints_return_seeded_telemetry(): void
    {
        $posts = $this->getJson('/api/v1/posts');
        $posts->assertOk();
        $posts->assertJsonCount(6, 'data');

        $single = $this->getJson('/api/v1/posts/gta-vi-price-editions-guide-standard-deluxe-collector');
        $single->assertOk();
        $single->assertJsonPath('data.slug', 'gta-vi-price-editions-guide-standard-deluxe-collector');
        $single->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'category',
                'tags',
                'meta' => ['meta_title', 'meta_description', 'schema_type'],
                'disclaimer',
            ],
        ]);
        $this->assertArrayNotHasKey('seo', $single->json('data'));
        $this->assertArrayNotHasKey('seo_score', $single->json('data'));
        $this->assertStringNotContainsString('seo_score', $single->getContent());

        $editions = $this->getJson('/api/v1/editions');
        $editions->assertOk();
        $editions->assertJsonCount(3, 'data');

        $platforms = $this->getJson('/api/v1/platforms');
        $platforms->assertOk();
        $platforms->assertJsonCount(6, 'data');
    }

    public function test_interactive_calculators_livewire_switches_tabs_and_updates_projections(): void
    {
        Livewire::test(InteractiveCalculators::class)
            ->assertSet('activeTab', 'download')
            ->set('speedValue', 1000.0)
            ->set('sizeGb', 200.0)
            ->assertSee('250 GB')
            ->call('selectTab', 'pc')
            ->assertSet('activeTab', 'pc')
            ->set('cpuTier', 'enthusiast')
            ->set('gpuTier', 'enthusiast')
            ->set('resolution', '1080p')
            ->set('rayTracing', false)
            ->assertSee('Vice Ultra Ready')
            ->call('selectTab', 'edition')
            ->set('currency', 'GBP')
            ->set('expectedPlayHours', 100)
            ->assertSee('£')
            ->call('selectTab', 'map')
            ->call('applyVehiclePreset', 380.0)
            ->assertSee('125');
    }

    public function test_blog_index_filter_livewire_searches_and_resets_filters(): void
    {
        Livewire::test(BlogIndexFilter::class)
            ->set('search', 'RAGE 9')
            ->assertSee('RAGE 9 Engine Analysis: Ray-Traced Global Illumination, Water & Hair Physics')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('sort', 'latest');
    }

    public function test_studio_post_editor_livewire_creates_publishes_logs_and_restores_revisions(): void
    {
        $this->actingAs(User::query()->where('email', 'admin@gtavi.pro')->firstOrFail());
        $category = Category::query()->firstOrFail();

        $component = Livewire::test(StudioPostEditor::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Leonida Nightlife Lighting & Vice Neon Benchmark')
            ->call('generateSlug')
            ->set('excerpt', 'Technical benchmark breakdown of nocturnal neon illumination, reflection probes, and streaming performance across Vice City districts.')
            ->set('focusKeyword', 'leonida nightlife lighting')
            ->set('tagsInput', 'leonida, tech-analysis, neon')
            ->call('addBlock', 'heading')
            ->call('addBlock', 'faq')
            ->call('autoFillSeo')
            ->set('editorNote', 'Initial release draft')
            ->call('savePost', 'published')
            ->assertHasNoErrors();

        $post = Post::query()->where('slug', 'leonida-nightlife-lighting-vice-neon-benchmark')->firstOrFail();
        $this->assertSame('published', $post->status);

        $firstRevision = PostRevision::query()->where('post_id', $post->id)->firstOrFail();

        $component
            ->set('title', 'Updated Temporary Headline V2')
            ->set('editorNote', 'Second edit')
            ->call('savePost', 'keep');

        $this->assertSame(2, PostRevision::query()->where('post_id', $post->id)->count());

        $component->call('restoreRevision', $firstRevision->id);

        $post->refresh();
        $this->assertSame('Leonida Nightlife Lighting & Vice Neon Benchmark', $post->title);
        $this->assertSame(3, PostRevision::query()->where('post_id', $post->id)->count());
    }

    public function test_studio_http_controller_crud_lifecycle(): void
    {
        $this->actingAs(User::query()->where('email', 'admin@gtavi.pro')->firstOrFail());
        $category = Category::query()->firstOrFail();

        $storeResponse = $this->post('/studio', [
            'category_id' => $category->id,
            'title' => 'Vice Port Storage Speed Verification Test',
            'excerpt' => 'Analyzing DirectStorage decompression budgets and NVMe throughput targets for projected Leonida urban zones.',
            'status' => 'draft',
            'verification_badge' => 'technical_projection',
            'schema_type' => 'TechArticle',
            'content_blocks' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Storage streaming analysis for modern PCIe Gen4 NVMe drives.',
                ],
            ],
            'tags' => ['storage', 'benchmark'],
        ]);

        $created = Post::query()->where('title', 'Vice Port Storage Speed Verification Test')->firstOrFail();
        $storeResponse->assertRedirect(route('studio.edit', $created));

        $updateResponse = $this->put('/studio/'.$created->id, [
            'category_id' => $category->id,
            'title' => 'Vice Port Storage Speed Verification Updated',
            'excerpt' => 'Updated analysis covering DirectStorage queue depth and decompression pipelines across modern platforms.',
            'status' => 'published',
            'verification_badge' => 'confirmed',
            'schema_type' => 'TechArticle',
            'content_blocks' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Updated storage streaming analysis with console baseline metrics.',
                ],
            ],
            'tags' => ['storage'],
        ]);

        $updateResponse->assertRedirect(route('studio.edit', $created));
        $this->assertSame('published', $created->fresh()?->status);

        $deleteResponse = $this->delete('/studio/'.$created->id);
        $deleteResponse->assertRedirect(route('studio.index'));
        $this->assertDatabaseMissing('posts', ['id' => $created->id]);
    }
}
