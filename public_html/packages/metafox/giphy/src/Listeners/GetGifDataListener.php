<?php

namespace MetaFox\Giphy\Listeners;

use MetaFox\Giphy\Repositories\GifRepositoryInterface;
use MetaFox\Platform\Contracts\User;

class GetGifDataListener
{
    public function handle(User $context, string $id)
    {
        return resolve(GifRepositoryInterface::class)->getGifData($context, $id);
    }
}
