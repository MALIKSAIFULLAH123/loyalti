<?php

namespace MetaFox\Photo\Repositories\Contracts;

use MetaFox\Platform\Contracts\User;

interface MediaAlbumRepositoryInterface
{
    /**
     * @param  User              $context
     * @param  User              $owner
     * @return array<int, mixed>
     */
    public function getAlbumsForForm(User $context, User $owner): array;
}
