<?php

namespace MetaFox\Announcement\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Announcement\Models\Announcement;
use MetaFox\Announcement\Models\AnnouncementView;
use MetaFox\Platform\Jobs\AbstractJob;

class RecountTotalViewJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle()
    {
        /*
         * Remove records from missing users
         */
        AnnouncementView::query()
            ->leftJoin('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('announcement_views.user_id', '=', 'user_entities.id');
            })
            ->whereNull('user_entities.id')
            ->delete();

        /*
         * Recount right total_view
         */
        Announcement::query()
            ->select(['announcements.id', DB::raw('COUNT(announcement_views.id) AS new_total_view')])
            ->join('announcement_views', function (JoinClause $joinClause) {
                $joinClause->on('announcement_views.announcement_id', '=', 'announcements.id');
            })
            ->join('user_entities', function (JoinClause $joinClause) {
                $joinClause->on('announcement_views.user_id', '=', 'user_entities.id');
            })
            ->groupBy(['announcements.id'])
            ->get()
            ->each(function ($announcement) {
                /*
                 * @var Announcement $announcement
                 */
                $announcement
                    ->fill(['total_view' => $announcement->new_total_view ?: 0])
                    ->saveQuietly();
            });
    }
}
