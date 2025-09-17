<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;

/**
 * @deprecated Remove in v5.2
 */
class GetIdsUserInviteListener
{
    public function __construct(protected PageInviteRepositoryInterface $inviteRepository)
    {
    }

    /**
     * @param mixed $owner
     *
     * @return array|null
     */
    public function handle($owner): ?array
    {
        if (!$owner instanceof Page) {
            return null;
        }
        $invite = $this->inviteRepository->getPendingInvites($owner);

        return $invite->collect()->pluck('owner_id')->toArray();
    }
}
