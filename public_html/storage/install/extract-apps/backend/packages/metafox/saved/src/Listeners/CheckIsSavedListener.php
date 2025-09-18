<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Saved\Listeners;

use MetaFox\Saved\Repositories\SavedRepositoryInterface;

/**
 * Class CheckIsSavedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CheckIsSavedListener
{
    /**
     * @param int    $userId
     * @param int    $itemId
     * @param string $itemType
     *
     * @return bool
     */
    public function handle(int $userId, int $itemId, string $itemType): bool
    {
        return resolve(SavedRepositoryInterface::class)->checkIsSaved($userId, $itemId, $itemType);
    }
}
