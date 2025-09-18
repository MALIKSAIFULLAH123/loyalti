<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Traits\FloodControlVideoTrait;
use MetaFox\Video\Traits\QuotaControlVideoTrait;

class PrePhotoAlbumUpdateListener
{
    use QuotaControlVideoTrait;
    use FloodControlVideoTrait;

    /**
     * @param User  $context
     * @param mixed $params
     * @return void
     */
    public function handle(User $context, mixed $params): void
    {
        if (!is_array($params)) {
            return;
        }

        $this->checkFloodControlWhenCreateVideo($context, $params, 'items.new');
        $this->checkQuotaControlWhenCreateVideo($context, $params, ['items.new', 'items.remove']);
    }
}
