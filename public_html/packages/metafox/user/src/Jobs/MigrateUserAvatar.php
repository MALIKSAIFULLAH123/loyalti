<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\UserProfile;

class MigrateUserAvatar extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        if (!DB::table('photo_albums')->exists()) {
            return;
        }
        $userProfile = UserProfile::query()
            ->select('user_profiles.id', 'storage_files.id as file_id')
            ->join('importer_entries', function (JoinClause $joinClause) {
                $joinClause->on('importer_entries.resource_id', '=', 'user_profiles.id')
                    ->where('importer_entries.resource_type', 'user');
            })
            ->join('storage_files', function (JoinClause $joinClause) {
                $joinClause->on('storage_files.id', '=', 'user_profiles.avatar_file_id');
            })
            ->whereNotNull('user_profiles.avatar_file_id')
            ->whereNull('user_profiles.avatar_id')
            ->orderBy('id')
            ->lazy();

        if (!$userProfile->count()) {
            return;
        }

        $collections = $userProfile->chunk(100);

        foreach ($collections as $collection) {
            MigrateChunkingUserAvatar::dispatch($collection->pluck('id')->toArray());
            MigrateChunkingUserAvatarFile::dispatch($collection->pluck('file_id')->toArray());
        }
    }
}
