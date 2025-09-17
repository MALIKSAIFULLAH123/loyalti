<?php

namespace MetaFox\Platform\Contracts\DataPrivacy;

use MetaFox\Platform\Contracts\User;

/**
 * Interface UserDataInterface
 * @package MetaFox\Platform\Contracts\DataPrivacy
 */
interface UserDataInterface
{
    /**
     * @param User $user
     * @todo NamNV consider to put to queue.
     */
    public function deleteAllBelongToUser(User $user): void;
}
