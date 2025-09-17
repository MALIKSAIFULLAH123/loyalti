<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Repositories\PhotoRepositoryInterface;

class MakeProfileAvatarListener
{
    /**
     * @param mixed $data
     *
     * @return array<string, mixed>
     */
    public function handle(...$data): ?array
    {
        return resolve(PhotoRepositoryInterface::class)->makeProfileAvatar(...$data);
    }
}
