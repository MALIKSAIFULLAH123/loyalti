<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Database\Eloquent\Builder;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Repositories\PageInviteRepositoryInterface;

class GetInviteBuilderListener
{
    public function __construct(protected PageInviteRepositoryInterface $inviteRepository)
    {
    }

    /**
     * @param mixed $owner
     *
     * @return array|null
     */
    public function handle($owner): ?Builder
    {
        if (!$owner instanceof Page) {
            return null;
        }
        $table = $this->inviteRepository->getModel()->getTable();

        return $this->inviteRepository->getBuilderPendingInvites($owner)->select("$table.owner_id");
    }
}
