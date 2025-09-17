<?php
namespace MetaFox\Featured\Listeners;

use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\Content;

class UnfeatureItemListener
{
    public function handle(User $user, Content $content): void
    {
        resolve(ItemRepositoryInterface::class)->markItemCancelledByUnFeaturingContent($user, $content);
    }
}
