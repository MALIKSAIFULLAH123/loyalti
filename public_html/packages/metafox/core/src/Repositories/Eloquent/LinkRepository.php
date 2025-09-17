<?php

namespace MetaFox\Core\Repositories\Eloquent;

use MetaFox\Core\Models\Link;
use MetaFox\Core\Repositories\LinkRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

class LinkRepository extends AbstractRepository implements LinkRepositoryInterface
{
    public function model()
    {
        return Link::class;
    }

    public function deleteUserData(int $userId): void
    {
        $links = $this->getModel()->newModelQuery()
            ->where([
                'user_id' => $userId,
            ])
            ->get();

        foreach ($links as $link) {
            $link->delete();
        }
    }

    public function deleteOwnerData(int $ownerId): void
    {
        $links = $this->getModel()->newModelQuery()
            ->where([
                'owner_id' => $ownerId,
            ])
            ->get();

        foreach ($links as $link) {
            $link->delete();
        }
    }
}
