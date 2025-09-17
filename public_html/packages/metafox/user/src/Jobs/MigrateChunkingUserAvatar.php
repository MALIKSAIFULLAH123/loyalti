<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use MetaFox\Photo\Models\Album;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Models\UserProfile;

/**
 * stub: packages/jobs/job-queued.stub.
 */
class MigrateChunkingUserAvatar extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected array $userIds)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!count($this->userIds)) {
            return;
        }
        $albums = DB::table('photo_albums')
            ->where('album_type', Album::PROFILE_ALBUM)
            ->whereNotNull('cover_photo_id')
            ->whereIn('owner_id', $this->userIds)
            ->where('owner_type', 'user')
            ->get(['cover_photo_id', 'owner_id', 'owner_type']);

        foreach ($albums as $album) {
            UserProfile::query()->where([
                'id' => $album->owner_id,
            ])->update(['avatar_id' => $album->cover_photo_id]);
            UserEntity::query()->where([
                'id'          => $album->owner_id,
                'entity_type' => $album->owner_type,
            ])->update(['avatar_id' => $album->cover_photo_id]);
        }
    }
}
