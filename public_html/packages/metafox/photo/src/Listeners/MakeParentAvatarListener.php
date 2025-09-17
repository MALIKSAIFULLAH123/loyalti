<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Repositories\PhotoRepositoryInterface;

class MakeParentAvatarListener
{
    /**
     * @param mixed $data
     *
     * @return ?array
     */
    public function handle(...$data): ?array
    {
        return resolve(PhotoRepositoryInterface::class)->makeParentAvatar(...$data);
    }
}
