<?php

namespace MetaFox\Music\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Repositories\PlaylistRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;

/**
 * Class DeleteAlbumJob.
 */
class DeleteUserDataJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private User $user)
    {
        parent::__construct();
    }

    public function uniqueId(): string
    {
        return 'metafox_music_' . __CLASS__ . '_' . $this->user->entityId();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $this->deleteUserAlbums($this->user);
        $this->deleteUserPlaylist($this->user);
        $this->deleteUserSong($this->user);
    }

    protected function deleteUserAlbums(User $user): void
    {
        resolve(AlbumRepositoryInterface::class)->deleteUserData($user);
        resolve(AlbumRepositoryInterface::class)->deleteOwnerData($user);
    }

    protected function deleteUserPlaylist(User $user): void
    {
        resolve(PlaylistRepositoryInterface::class)->deleteUserData($user);
    }

    protected function deleteUserSong(User $user): void
    {
        resolve(SongRepositoryInterface::class)->deleteUserData($user);
        resolve(SongRepositoryInterface::class)->deleteOwnerData($user);
    }
}
