<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Repositories\Contracts\PrivacyRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;

class CreatePrivacyStreamListener
{
    public function handle(Entity $entity): void
    {
        resolve(PrivacyRepositoryInterface::class)->forceCreatePrivacyStream($entity);
    }
}
