<?php

namespace MetaFox\Video\Listeners;

use MetaFox\Platform\Contracts\User;
use MetaFox\Video\Traits\QuotaControlVideoTrait;

class PreComposerCreateListener
{
    use QuotaControlVideoTrait;

    /**
     * @param  User  $context
     * @param  mixed $params
     * @return void
     */
    public function handle(User $context, mixed $params): void
    {
        if (!is_array($params)) {
            return;
        }

        $this->checkQuotaControlWhenCreateVideo($context, $params, ['photo_files.new', 'photo_files.remove']);
    }
}
