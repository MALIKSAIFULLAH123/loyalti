<?php

namespace MetaFox\Core\Listeners;

use MetaFox\User\Models\User;

class ParseUrlListener
{
    /**
     * @param  string     $url
     * @param  User|null  $context
     * @param  array      $params
     * @return array|null
     */
    public function handle(string $url, ?User $context = null, array $params = []): ?array
    {
        $data = app('events')->dispatch('core.process_parse_url', [$url, $context, $params], true);

        if (empty($data)) {
            $data = app('events')->dispatch('core.after_parse_url', [$url, $context, $params], true);
        }

        return $data;
    }
}
