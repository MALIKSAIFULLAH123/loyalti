<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Support\Link\FetchLink;
use MetaFox\Core\Support\Link\Providers\General;

class ParseGenericUrlListener
{
    /**
     * @param string $url
     *
     * @return ?array<mixed>
     */
    public function handle(string $url): ?array
    {
        $provider = new General();

        $service = new FetchLink($provider);

        $data = $service->parse($url);

        if (null === $data) {
            $data = $service->getDefault($url);
        }

        return $data;
    }
}
