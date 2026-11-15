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
                'description' => 'Rockstar Games publicly confirmed for the first time that active development on the next entry in the Grand Theft Auto series was well underway.',
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
                'description' => 'Trailer 1 introduced Lucia Caminos, Jason Duval, modern Vice City, and the State of Leonida while confirming PlayStation 5 and Xbox Series X|S as launch platforms.',
                'impact_level' => 'critical',
                'sort_order' => 2,
            ],
            [
                'title' => 'Take-Two Narrowed Initial Window to Fall 2025',
                'slug' => 'take-two-fall-2025-guidance',
                'milestone_date' => '2024-05-16',
                'display_date_label' => 'May 16, 2024',
                'category' => 'earnings_call',
                'source_name' => 'Take-Two Interactive FY24 Q4 Release',
                'source_url' => 'https://ir.take2games.com/',
                'is_confirmed' => true,
                'description' => 'Take-Two Interactive narrowed its initial calendar 2025 outlook to Fall 2025 for PlayStation 5 and Xbox Series X|S.',
                'impact_level' => 'major',
                'sort_order' => 3,
            ],
            [
                'title' => 'First Release Date Shift to May 26, 2026',
                'slug' => 'may-2026-release-date-update',
                'milestone_date' => '2025-05-02',
                'display_date_label' => 'May 2, 2025',
                'category' => 'official_announcement',
                'source_name' => 'Rockstar Games Newswire',
                'source_url' => 'https://www.rockstargames.com/newswire',
                'is_confirmed' => true,
                'description' => 'Rockstar Games announced an updated launch date of May 26, 2026, stating that extra development time was needed to meet expected polish.',
                'impact_level' => 'major',
                'sort_order' => 4,
            ],
            [
                'title' => 'Official Trailer 2 & Six-Region Leonida Showcase',
                'slug' => 'official-trailer-2-leonida-showcase',
                'milestone_date' => '2025-05-06',
                'display_date_label' => 'May 6, 2025',
                'category' => 'trailer',
                'source_name' => 'Rockstar Games Newswire & Official Site',
                'source_url' => 'https://www.rockstargames.com/VI',
                'is_confirmed' => true,
                'description' => 'Trailer 2 debuted with footage captured entirely on base PlayStation 5, accompanied by expanded site dossiers on Jason Duval, Lucia Caminos, and six Leonida regions.',
                'impact_level' => 'critical',
                'sort_order' => 5,
            ],
            [
                'title' => 'Official Release Date Updated to November 19, 2026',
                'slug' => 'november-19-2026-release-date-confirmed',
                'milestone_date' => '2025-11-06',
                'display_date_label' => 'Nov 6, 2025',
                'category' => 'official_announcement',
                'source_name' => 'Rockstar Games Newswire & Take-Two FY26 Q2',
                'source_url' => 'https://www.rockstargames.com/newswire',
                'is_confirmed' => true,
                'description' => 'Rockstar Games and Take-Two Interactive moved the worldwide console release date to November 19, 2026 to finish final polish and QA.',
                'impact_level' => 'critical',
                'sort_order' => 6,
            ],
            [
                'title' => 'PlayStation 5 & Xbox Series X|S Console Launch',
                'slug' => 'console-launch-window-tracking',
                'milestone_date' => '2026-11-19',
                'display_date_label' => 'Nov 19, 2026 (Official Console Date)',
                'category' => 'launch_window',
                'source_name' => 'Rockstar Games Official Launch Target',
                'source_url' => 'https://www.rockstargames.com/VI',
                'is_confirmed' => true,
                'description' => 'Scheduled worldwide PlayStation 5 and Xbox Series X|S launch date, with digital storefront pre-loading typically opening 48 to 72 hours before local midnight.',
                'impact_level' => 'critical',
                'sort_order' => 7,
            ],
            [
                'title' => 'PC Edition & DirectStorage Technical Wave',
                'slug' => 'pc-edition-projected-wave',
                'milestone_date' => '2027-11-19',
                'display_date_label' => 'Late 2027 – Early 2028 (Projected)',
                'category' => 'launch_window',
                'source_name' => 'Historical Rockstar Release Cadence Model',
                'source_url' => null,
                'is_confirmed' => false,
                'description' => 'Rockstar has not announced a PC release date. Based on GTA V and Red Dead Redemption 2 precedent, a dedicated PC release is modeled 12 to 18 months after consoles.',
                'impact_level' => 'major',
                'sort_order' => 8,
            ],
        ];

        ReleaseMilestone::query()
            ->whereIn('slug', ['take-two-fy25-reaffirmation', 'console-preload-staging-window'])
            ->delete();

        foreach ($milestones as $milestone) {
            ReleaseMilestone::query()->updateOrCreate(
                ['slug' => $milestone['slug']],
                $milestone
            );
        }
    }
}
