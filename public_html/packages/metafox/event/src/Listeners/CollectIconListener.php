<?php

namespace MetaFox\Event\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Event\Support\Facades\Event;

class CollectIconListener
{
    public function handle(): ?array
    {
        $items = Event::getPrivacyList();

        if (!is_array($items)) {
            return null;
        }

        $collects = [];

        foreach ($items as $item) {
            if (Arr::has($item, ['privacy', 'privacy_type', 'privacy_icon'])) {
                $collects[Arr::get($item, 'privacy_type')] = $item;
            }
        }

        return $collects;
    }
}
