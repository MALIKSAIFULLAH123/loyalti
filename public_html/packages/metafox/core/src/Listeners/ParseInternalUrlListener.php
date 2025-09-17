<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Support\Link\FetchLink;
use MetaFox\Core\Support\Link\Providers\Internal;
use MetaFox\User\Models\User;

class ParseInternalUrlListener
{
    /**
     * @param  string     $url
     * @param  User|null  $context
     * @param  array      $params
     * @return array|null
     */
    public function handle(string $url, ?User $context, array $params): ?array
    {
        if (!$context instanceof User) {
            return null;
        }

        $service = new Internal($context, $params);

        return (new FetchLink($service))->parse($url);
    }
}
