<?php

namespace MetaFox\Platform\Contracts\Policy;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Interface SaveItemPolicyInterface
 * @package MetaFox\Platform\Contracts\Policy
 */
interface SaveItemPolicyInterface
{
    /**
     * @param User         $user
     * @param Content|null $resource
     *
     * @return bool
     */
    public function saveItem(User $user, Content $resource = null): bool;

    /**
     * @param User    $user
     * @param Content $resource
     *
     * @return bool
     */
    public function isSavedItem(User $user, Content $resource): bool;
}
