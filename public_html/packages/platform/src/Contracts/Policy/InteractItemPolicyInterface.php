<?php

namespace MetaFox\Platform\Contracts\Policy;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;

/**
 * Interface InteractItemPolicyInterface
 * @package MetaFox\Platform\Contracts\Policy
 */
interface InteractItemPolicyInterface
{
    /**
     * @param User         $user
     * @param Content|null $resource
     *
     * @return bool
     */
    public function like(User $user, ?Content $resource = null): bool;

    /**
     * @param User         $user
     * @param Content|null $resource
     *
     * @return bool
     */
    public function share(User $user, ?Content $resource = null): bool;

    /**
     * @param User         $user
     * @param Content|null $resource
     *
     * @return bool
     */
    public function comment(User $user, ?Content $resource = null): bool;
}
