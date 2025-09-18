<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group;

class SearchOwnerOptionListener
{
    public function handle(): array
    {
        return ['label' => __p('group::phrase.group'), 'value' => Group::ENTITY_TYPE];
    }
}
