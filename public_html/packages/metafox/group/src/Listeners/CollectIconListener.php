<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Group\Support\Facades\Group;

class CollectIconListener
{
    public function handle(): ?array
    {
        $items = Group::getPrivacyList();

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
