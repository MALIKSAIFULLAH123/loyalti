<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Support\Link\FetchLink;
use MetaFox\Core\Support\Link\Providers\Vimeo;
use MetaFox\Platform\Facades\Settings;

class ParseVimeoUrlListener
{
    /**
     * @param string $url
     *
     * @return ?array<mixed>
     */
    public function handle(string $url): ?array
    {
        $config = [
            'client_id'     => Settings::get('core.services.vimeo.client_id') ?: 'af00174f628d2da1e1d73f69bd2f44eab3f3abc4',
            'client_secret' => Settings::get('core.services.vimeo.client_secret') ?: '7eRvz/d2p5Zw7WcW3cou4Qh9p8xlAo0NUjabYvZfq/J1QgxFa5gUIOXKRQcaEwfEPfga8Kym7tD0KYCMrmGqEO/Xc4p+vyKnsfXeRRUpf1h0HOGn1ZGUDwGsnRv7dgGU',
            'access_token'  => Settings::get('core.services.vimeo.access_token') ?: '6d16203c074e50fd9a916a0de5c5410b',
        ];

        $service = new Vimeo($config);

        return (new FetchLink($service))->parse($url);
    }
}
