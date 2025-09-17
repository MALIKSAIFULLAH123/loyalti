<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Support\Link\FetchLink;
use MetaFox\Core\Support\Link\Providers\Facebook;
use MetaFox\Core\Support\Link\Providers\Tiktok;
use MetaFox\Platform\Facades\Settings;

class ParseTiktokUrlListener
{
    /**
     * @param string $url
     *
     * @return ?array<mixed>
     */
    public function handle(string $url): ?array
    {
        $service = new Tiktok();

        return (new FetchLink($service))->parse($url);
    }
}
