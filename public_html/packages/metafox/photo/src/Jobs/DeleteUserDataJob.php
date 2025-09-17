<?php

namespace MetaFox\Photo\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
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

    private User $user;

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
    }

    public function uniqueId(): string
    {
        return 'metafox_photo_' . __CLASS__ . '_' . $this->user->entityId();
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
        $this->deleteUserPhotoGroups($this->user);
    }

    protected function deleteUserAlbums(User $user): void
    {
        resolve(AlbumRepositoryInterface::class)->deleteUserData($user);
        resolve(AlbumRepositoryInterface::class)->deleteOwnerData($user);
    }

    protected function deleteUserPhotoGroups(User $user): void
    {
        resolve(PhotoGroupRepositoryInterface::class)->deleteUserPhotoGroups($user);
    }
}
