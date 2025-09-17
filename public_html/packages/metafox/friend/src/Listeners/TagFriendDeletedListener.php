<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Friend\Listeners;

use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasAmounts;
use MetaFox\Platform\Contracts\HasTaggedFriend;

class TagFriendDeletedListener
{
    public function handle(Entity $resource, array $attribute): void
    {
        if (count($attribute) < 1) {
            return;
        }

        if (!$resource instanceof HasTaggedFriend) {
            return;
        }

        if ($resource instanceof HasAmounts){
            $resource->decrementAmount('total_tag_friend',count($attribute));
        }
    }
}
