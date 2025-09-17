<?php

namespace MetaFox\Photo\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\AlbumItem;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Repositories\PhotoGroupRepositoryInterface;
use MetaFox\Platform\Contracts\HasAvatarMorph;
use MetaFox\Platform\Contracts\Media;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class UpdateUserJob.
 */
class UpdateUserJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * DeleteCategoryJob constructor.
     *
     * @param int $ownerId
     */
    public function __construct(protected int $ownerId)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $owner = null;
        try {
            $owner = UserEntity::getById($this->ownerId)?->detail;
        } catch (\Throwable) {
        }

        if (!$owner instanceof User) {
            return;
        }

        $this->handleAlbumByType($owner);

        if ($owner instanceof HasAvatarMorph) {
            $this->handleAlbumByType($owner, Album::PROFILE_ALBUM);
        }
    }

    protected function handleAlbumByType(User $owner, string $albumType = Album::COVER_ALBUM): void
    {
        /**@var $albumRepository AlbumRepositoryInterface */
        $albumRepository = resolve(AlbumRepositoryInterface::class);
        $album           = $albumRepository->getModel()->newQuery()
            ->where('owner_id', $owner->entityId())
            ->where('album_type', $albumType)
            ->first();

        if (!$album instanceof Album) {
            return;
        }

        $album->update(['user_id' => $owner->userId()]);
        $this->handleAlbumItem($album, $owner);
        $this->handlePhotoGroup($album, $owner);
    }

    protected function handleAlbumItem(Album $album, User $owner): void
    {
        $albumItem = $album->items()->get()->collect();

        if ($albumItem->isEmpty()) {
            return;
        }

        $albumItem->each(function (mixed $item) use ($owner) {
            if (!$item instanceof AlbumItem) {
                return;
            }

            $media = $item->detail()->first();
            if ($media instanceof Media) {
                $media->update(['user_id' => $owner->userId()]);
            }
        });
    }

    protected function handlePhotoGroup(Album $album, User $owner): void
    {
        /**@var $photoGroupRepository PhotoGroupRepositoryInterface */
        $photoGroupRepository = resolve(PhotoGroupRepositoryInterface::class);
        $photoGroupRepository->getModel()->newQuery()
            ->where('album_id', $album->entityId())
            ->update(['user_id' => $owner->userId()]);
    }
}
