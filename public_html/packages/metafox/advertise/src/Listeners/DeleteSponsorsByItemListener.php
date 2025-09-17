<?php

namespace MetaFox\Advertise\Listeners;

use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;
use MetaFox\Platform\Contracts\Content;

class DeleteSponsorsByItemListener
{
    public function handle(Content $content): void
    {
        resolve(SponsorRepositoryInterface::class)->deleteDataByItem($content);
    }
}
