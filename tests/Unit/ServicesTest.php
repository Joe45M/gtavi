<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\GameEdition;
use App\Models\MapLocation;
use App\Services\CalculatorService;
use App\Services\ContentBlockService;
use App\Services\SeoService;
use Tests\TestCase;

final class ServicesTest extends TestCase
{
    public function test_calculator_service_computes_download_time_and_storage_buffer(): void
    {
        $service = new CalculatorService();

        $result = $service->calculateDownloadTime(
            speedValue: 100.0,
            unit: 'Mbps',
            sizeGb: 160.0,
            overheadPercent: 8.0,
            dataCapGb: 800,
        );

        $this->assertSame(100.0, $result['raw_mbps']);
        $this->assertSame(92.0, $result['effective_mbps']);
        $this->assertSame(11.5, $result['effective_mb_per_sec']);
        $this->assertGreaterThan(13000, $result['total_seconds']);
        $this->assertSame(200, $result['recommended_free_ssd_gb']);
        $this->assertSame(20.0, $result['data_cap_percent']);
        $this->assertCount(4, $result['tiers']);
    }

    public function test_calculator_service_estimates_pc_readiness_and_vram_bottleneck(): void
    {
        $service = new CalculatorService();

        $balanced = $service->estimatePcReadiness(
            cpuTier: 'high',
            gpuTier: 'enthusiast',
            ramGb: 32,
            vramGb: 16,
            resolution: '1440p',
            rayTracing: true,
            upscaler: 'dlss_quality',
        );

        $this->assertGreaterThanOrEqual(80, $balanced['readiness_score']);
        $this->assertSame(12, $balanced['vram_required_gb']);
        $this->assertSame(4, $balanced['vram_headroom_gb']);
        $this->assertGreaterThan($balanced['estimated_fps']['min'], $balanced['estimated_fps']['avg']);
        $this->assertGreaterThan($balanced['estimated_fps']['avg'], $balanced['estimated_fps']['max']);

        $vramLimited = $service->estimatePcReadiness(
            cpuTier: 'mid',
            gpuTier: 'mid',
            ramGb: 16,
            vramGb: 8,
            resolution: '4k',
            rayTracing: true,
            upscaler: 'native',
        );

        $this->assertStringContainsString('VRAM', $vramLimited['bottleneck']);
        $this->assertSame(-8, $vramLimited['vram_headroom_gb']);
    }

    public function test_calculator_service_evaluates_edition_value_and_map_scale(): void
    {
        $service = new CalculatorService();

        $edition = new GameEdition([
            'name' => 'Vice Deluxe Edition',
            'price_usd' => 99.99,
            'price_gbp' => 89.99,
            'price_eur' => 99.99,
            'price_cad' => 129.99,
            'price_aud' => 149.99,
            'price_jpy' => 13800,
            'estimated_size_gb' => 165,
            'early_access_hours' => 72,
            'online_bonus_credits' => 2500000,
            'digital_bonuses' => ['Starter Pack', 'Leonida Map'],
            'physical_collectibles' => [],
        ]);

        $gbpValue = $service->calculateEditionValue($edition, 'GBP', 100);
        $this->assertSame('£89.99', $gbpValue['formatted_price']);
        $this->assertSame('£0.90', $gbpValue['formatted_cost_per_hour']);
        $this->assertSame('Best Launch-Week Value', $gbpValue['value_verdict']);
        $this->assertSame(2, $gbpValue['bonus_items_count']);

        $location = new MapLocation([
            'name' => 'Vice City Metro',
            'estimated_area_sq_km' => 42.5,
        ]);

        $map = $service->calculateMapComparison(240.0, $location);
        $this->assertSame(125.0, $map['leonida_area_sq_km']);
        $this->assertGreaterThan(1.5, $map['multiplier_vs_gta5']);
        $this->assertSame('Vice City Metro', $map['selected_region_name']);
        $this->assertGreaterThan(0.0, $map['selected_region_minutes']);
    }

    public function test_content_block_service_renders_accessible_tables_faqs_and_sanitizes_xss_links(): void
    {
        $blocksService = new ContentBlockService();

        $blocks = [
            [
                'type' => 'heading',
                'level' => 2,
                'text' => 'Leonida Storage & File Matrix',
            ],
            [
                'type' => 'paragraph',
                'text' => 'Inspect <script>alert(1)</script> [Malicious](javascript:alert(1)) and [Safe Calc](/calculators).',
            ],
            [
                'type' => 'spec_table',
                'caption' => 'Platform Comparison',
                'headers' => ['Platform', 'Estimated Footprint'],
                'rows' => [
                    ['PS5', '155 GB'],
                ],
            ],
            [
                'type' => 'faq',
                'heading' => 'Technical FAQ',
                'items' => [
                    [
                        'question' => 'Is pre-load mandatory?',
                        'answer' => 'No, digital stores stream on launch day too.',
                    ],
                ],
            ],
        ];

        $html = $blocksService->renderBlocks($blocks);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringContainsString('<a href="/calculators" class="article-link">Safe Calc</a>', $html);
        $this->assertStringContainsString('<div tabindex="0" role="region"', $html);
        $this->assertStringContainsString('class="table-wrapper"', $html);
        $this->assertStringContainsString('--label-1: &quot;Platform&quot;', $html);
        $this->assertStringContainsString('<details name="post-faq-3" class="vice-faq-item">', $html);

        $metrics = $blocksService->calculateMetrics($blocks);
        $this->assertGreaterThan(15, $metrics['word_count']);
        $this->assertSame(1, $metrics['reading_time_minutes']);

        $toc = $blocksService->extractTableOfContents($blocks);
        $this->assertCount(1, $toc);
        $this->assertSame('leonida-storage-file-matrix', $toc[0]['id']);

        $faqs = $blocksService->extractFaqItems($blocks);
        $this->assertCount(1, $faqs);
        $this->assertSame('Is pre-load mandatory?', $faqs[0]['question']);
    }

    public function test_seo_service_scores_content_and_builds_graph_json_ld(): void
    {
        $blocksService = new ContentBlockService();
        $seoService = new SeoService($blocksService);

        $sentence = 'Leonida bandwidth planning keeps SSD headroom healthy during launch week. ';
        $chunk = str_repeat($sentence, 9);

        $analysis = $seoService->analyzeContent([
            'title' => 'GTA VI Download Size Guide for 2026',
            'slug' => 'gta-vi-download-size-guide',
            'focus_keyword' => 'gta vi download size',
            'meta_title' => 'GTA VI Download Size Guide & Pre-Load Calculator (2026)',
            'meta_description' => 'Plan your storage and download time with our GTA VI download size guide, SSD headroom math, and regional pre-load projections.',
            'content_blocks' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Planning around the projected gta vi download size starts with SSD headroom and bandwidth math.',
                ],
                [
                    'type' => 'heading',
                    'level' => 2,
                    'text' => 'How GTA VI Download Size Scales on Console',
                ],
                [
                    'type' => 'paragraph',
                    'text' => $chunk.'Use our [Download Calculator](/calculators).',
                ],
                [
                    'type' => 'heading',
                    'level' => 2,
                    'text' => 'Recommended Pre-Load Storage Strategy',
                ],
                [
                    'type' => 'paragraph',
                    'text' => str_repeat($sentence, 45),
                ],
                [
                    'type' => 'spec_table',
                    'caption' => 'Storage Specs',
                    'headers' => ['Console', 'Projected GB'],
                    'rows' => [['PS5', '155 GB']],
                ],
            ],
        ]);

        $this->assertGreaterThanOrEqual(90, $analysis['seo_score']);
        $this->assertGreaterThanOrEqual(60, $analysis['readability_score']);
        $this->assertGreaterThan(550, $analysis['word_count']);

        $meta = $seoService->buildPageMeta(
            title: 'Test Page | GTAVI.PRO',
            description: 'Independent telemetry summary.',
            canonicalUrl: 'https://gtavi.pro/test',
            breadcrumbs: [
                ['name' => 'Home', 'url' => 'https://gtavi.pro/'],
                ['name' => 'Test', 'url' => 'https://gtavi.pro/test'],
            ],
            faqItems: [
                ['question' => 'Is this official?', 'answer' => 'No, GTAVI.PRO is independent.'],
            ],
        );

        $this->assertSame('https://schema.org', $meta['json_ld']['@context']);
        $types = array_column($meta['json_ld']['@graph'], '@type');
        $this->assertContains('WebSite', $types);
        $this->assertContains('Organization', $types);
        $this->assertContains('BreadcrumbList', $types);
        $this->assertContains('FAQPage', $types);
    }
}
