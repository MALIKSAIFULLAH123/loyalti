<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Listeners;

use MetaFox\Music\Models\Song;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Repositories\SongRepositoryInterface;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;

/**
 * Class UpdateFeedItemPrivacyListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateFeedItemPrivacyListener
{
    public function __construct(
        protected SongRepositoryInterface $songRepository,
        protected AlbumRepositoryInterface $albumRepository
    ) {
    }

    /**
     * @param  int        $itemId
     * @param  string     $itemType
     * @param  int        $privacy
     * @param  int[]      $list
     * @throws \Exception
     */
    public function handle(int $itemId, string $itemType, int $privacy, array $list = []): void
    {
        if (!in_array($itemType, [Song::ENTITY_TYPE, Album::ENTITY_TYPE])) {
            return;
        }

        $item = match ($itemType) {
            Song::ENTITY_TYPE  => $this->songRepository->find($itemId),
            Album::ENTITY_TYPE => $this->albumRepository->find($itemId)
        };

        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
