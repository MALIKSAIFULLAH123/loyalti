<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Group\Support\Support;

class SharedDataPreparationListener
{
    public function handle(string $postType, array $data): ?array
    {
        if (Support::SHARED_TYPE !== $postType) {
            return null;
        }

        $groups = Arr::get($data, 'groups', []);

        if (is_array($groups) && count($groups)) {
            Arr::set($data, 'owners', $groups);

            Arr::set($data, 'success_message', __p('group::phrase.shared_to_your_group'));

            unset($data['groups']);
        }

        return $data;
    }
}
