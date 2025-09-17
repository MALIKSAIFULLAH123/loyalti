<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Support\Link\FetchLink;
use MetaFox\Core\Support\Link\Providers\Facebook;
use MetaFox\Platform\Facades\Settings;

class ParseFacebookUrlListener
{
    /**
     * @param string $url
     *
     * @return ?array<mixed>
     */
    public function handle(string $url): ?array
    {
        $appId     = Settings::get('core.services.facebook.app_id') ?: '484766443667859';
        $appSecret = Settings::get('core.services.facebook.app_secret') ?: '525caed83174ec37ff69ee9a2f2bce53';

        $service = new Facebook([
            'app_id'     => $appId,
            'app_secret' => $appSecret,
        ]);

        return (new FetchLink($service))->parse($url);
    }
}
