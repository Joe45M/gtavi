<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ReleaseMilestone;
use Illuminate\Database\Seeder;

final class ReleaseMilestoneSeeder extends Seeder
{
    public function run(): void
    {
        $milestones = [
            [
                'title' => 'Active Development Officially Confirmed',
                'slug' => 'active-development-confirmed-2022',
                'milestone_date' => '2022-02-04',
                'display_date_label' => 'Feb 4, 2022',
                'category' => 'official_announcement',
                'source_name' => 'Rockstar Games Newswire',
                'source_url' => 'https://www.rockstargames.com/newswire',
                'is_confirmed' => true,
                'description' => 'Rockstar Games publicly confirmed for the first time that active development for the next entry in the Grand Theft Auto series was well underway.',
                'impact_level' => 'critical',
                'sort_order' => 1,
            ],
            [
                'title' => 'Official Reveal Trailer 1 & State of Leonida Unveiled',
                'slug' => 'official-trailer-1-december-2023',
                'milestone_date' => '2023-12-04',
                'display_date_label' => 'Dec 4, 2023',
                'category' => 'trailer',
                'source_name' => 'Rockstar Games Official Channel',
                'source_url' => 'https://www.rockstargames.com/VI',
                'is_confirmed' => true,
                'description' => 'Trailer 1 introduced Lucia, modern Vice City, and the State of Leonida, setting YouTube single-day non-music viewership records and confirming initial PS5 & Xbox Series X|S platforms.',
                'impact_level' => 'critical',
                'sort_order' => 2,
            ],
            [
                'title' => 'Take-Two Narrowed Initial Window to Fall 2025',
                'slug' => 'take-two-fall-2025-guidance',
                'milestone_date' => '2024-05-16',
                'display_date_label' => 'May 16, 2024',
                'category' => 'earnings_call',
                'source_name' => 'Take-Two Interactive FY24 Q4 Earnings Release',
                'source_url' => 'https://ir.take2games.com/',
                'is_confirmed' => true,
                'description' => 'Take-Two Interactive updated its investor outlook, narrowing the initial calendar 2025 window to Fall 2025 for PlayStation 5 and Xbox Series X|S.',
                'impact_level' => 'major',
                'sort_order' => 3,
            ],
            [
                'title' => 'Fall 2025 Window Reaffirmed Across FY25 Quarters',
                'slug' => 'take-two-fy25-reaffirmation',
                'milestone_date' => '2024-11-06',
                'display_date_label' => 'Nov 2024',
                'category' => 'earnings_call',
                'source_name' => 'Take-Two Interactive Investor Guidance',
                'source_url' => 'https://ir.take2games.com/',
                'is_confirmed' => true,
                'description' => 'Subsequent quarterly disclosures reiterated Take-Two net bookings expectations tied to the console launch cycle while Rockstar maintained polish silence.',
                'impact_level' => 'standard',
                'sort_order' => 4,
            ],
            [
                'title' => 'PlayStation 5 & Xbox Series X|S Console Launch Window',
                'slug' => 'console-launch-window-tracking',
                'milestone_date' => '2026-05-26',
                'display_date_label' => 'Console Target (Verify Official Newswire)',
                'category' => 'launch_window',
                'source_name' => 'Independent Release Tracker',
                'source_url' => null,
                'is_confirmed' => false,
                'description' => 'GTAVI.PRO tracks official Rockstar Games Newswire and Take-Two Interactive IR updates for exact street dates, pre-order store rollouts, and regional midnight unlock schedules.',
                'impact_level' => 'critical',
                'sort_order' => 5,
            ],
            [
                'title' => 'Console Pre-Load Window & Day-One Patch Staging',
                'slug' => 'console-preload-staging-window',
                'milestone_date' => '2026-11-15',
                'display_date_label' => 'T-72 Hours Pre-Launch (Projected)',
                'category' => 'launch_window',
                'source_name' => 'GTAVI.PRO Telemetry Estimate',
                'source_url' => null,
                'is_confirmed' => false,
                'description' => 'Historically, major Rockstar releases open PSN and Xbox Store pre-loading 48 to 96 hours prior to local midnight unlock to absorb 150+ GB CDN bursts.',
                'impact_level' => 'major',
                'sort_order' => 6,
            ],
            [
                'title' => 'PC Edition & DirectStorage Technical Wave',
                'slug' => 'pc-edition-projected-wave',
                'milestone_date' => '2027-06-01',
                'display_date_label' => '+12 to +18 Months Post-Console (Projected)',
                'category' => 'launch_window',
                'source_name' => 'Historical Rockstar Release Cadence Model',
                'source_url' => null,
                'is_confirmed' => false,
                'description' => 'Following GTA V and Red Dead Redemption 2 precedent, a dedicated PC version featuring unlocked frame rates, path-traced lighting, and DirectStorage is projected 12–18 months after console.',
                'impact_level' => 'major',
                'sort_order' => 7,
            ],
        ];

        foreach ($milestones as $milestone) {
            ReleaseMilestone::query()->updateOrCreate(
                ['slug' => $milestone['slug']],
                $milestone
            );
        }
    }
}
