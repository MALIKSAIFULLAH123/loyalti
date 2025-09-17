<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Quiz\Listeners;

use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;

/**
 * Class UpdateFeedItemPrivacyListener.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateFeedItemPrivacyListener
{
    public function __construct(protected QuizRepositoryInterface $repository)
    {
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
        if ($itemType != Quiz::ENTITY_TYPE) {
            return;
        }

        $item          = $this->repository->find($itemId);
        $item->privacy = $privacy;
        $item->setPrivacyListAttribute($list);
        $item->save();
    }
}
