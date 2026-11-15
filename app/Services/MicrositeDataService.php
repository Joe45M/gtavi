<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\GameEdition;
use App\Models\MapLocation;
use App\Models\PlatformSpec;
use App\Models\Post;
use App\Models\ReleaseMilestone;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class MicrositeDataService
{
    /**
     * @return Collection<int, GameEdition>
     */
    public function editions(): Collection
    {
        return GameEdition::query()->ordered()->get();
    }

    /**
     * @return Collection<int, PlatformSpec>
     */
    public function platformSpecs(?string $family = null): Collection
    {
        return PlatformSpec::query()
            ->when($family !== null, fn ($q) => $q->where('platform_family', $family))
            ->ordered()
            ->get();
    }

    /**
     * @return Collection<int, ReleaseMilestone>
     */
    public function milestones(): Collection
    {
        return ReleaseMilestone::query()->ordered()->get();
    }

    /**
     * @return Collection<int, MapLocation>
     */
    public function mapLocations(): Collection
    {
        return MapLocation::query()->ordered()->get();
    }

    /**
     * @return Collection<int, Category>
     */
    public function categories(): Collection
    {
        return Category::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->ordered()
            ->get();
    }

    /**
     * @return Collection<int, Tag>
     */
    public function tags(): Collection
    {
        return Tag::query()
            ->withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function homeSummary(): array
    {
        $stats = Cache::remember(
            'microsite.home_summary',
            now()->addMinutes(15),
            static fn (): array => [
                'published_articles' => Post::query()->published()->count(),
                'total_leonida_area' => (float) MapLocation::query()->sum('estimated_area_sq_km'),
                'avg_seo_score' => (int) round((float) (Post::query()->published()->avg('seo_score') ?? 94)),
                'platforms_tracked' => PlatformSpec::query()->count(),
            ]
        );

        return [
            'featured_post' => Post::query()->published()->featured()->with(['category', 'tags'])->orderByDesc('published_at')->first()
                ?? Post::query()->published()->with(['category', 'tags'])->orderByDesc('published_at')->first(),
            'latest_posts' => Post::query()->published()->with(['category', 'tags'])->orderByDesc('published_at')->limit(6)->get(),
            'editions' => $this->editions(),
            'platforms' => $this->platformSpecs(),
            'milestones' => $this->milestones(),
            'locations' => $this->mapLocations(),
            'stats' => $stats,
        ];
    }
}
