<?php

namespace MetaFox\Advertise\Listeners;

use MetaFox\Platform\Contracts\Content;

class SponsorApprovedListener
{
    public function handle(Content $content): void
    {
        $content->update(['is_sponsor' => true]);
    }
}
