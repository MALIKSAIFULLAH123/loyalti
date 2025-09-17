<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Support\Link\FetchLink;
use MetaFox\Core\Support\Link\Providers\Youtube;
use MetaFox\Platform\Facades\Settings;

class ParseYouTubeUrlListener
{
    /**
     * @param string $url
     *
     * @return ?array<mixed>
     */
    public function handle(string $url): ?array
    {
        $service = new Youtube([
            'api_key' => Settings::get('core.services.youtube.api_key') ?: 'AIzaSyA-pIQldPRcIDyKk_xe5Fl9YIkGhF-B7os',
        ]);

        return (new FetchLink($service))->parse($url);
    }
}
