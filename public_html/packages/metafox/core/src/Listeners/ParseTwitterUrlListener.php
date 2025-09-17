<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Support\Link\FetchLink;
use MetaFox\Core\Support\Link\Providers\Twitter;
use MetaFox\Platform\Facades\Settings;

class ParseTwitterUrlListener
{
    /**
     * @param string $url
     *
     * @return ?array<mixed>
     */
    public function handle(string $url): ?array
    {
        $config = [
            'api_key'    => Settings::get('core.services.twitter.api_key') ?: 'kHpFx7kigbz8htvQ3cPc9PXqC',
            'secret_key' => Settings::get('core.services.twitter.secret_key') ?: '21W7tzWW3AfkVnQjXmlvJhDxaTaxVKAR0BHHqJMzbGKCQB8GS4',
        ];

        $service = new Twitter($config);

        return (new FetchLink($service))->parse($url);
    }
}
