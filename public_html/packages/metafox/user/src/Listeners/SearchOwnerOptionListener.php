<?php

namespace MetaFox\User\Listeners;

use MetaFox\User\Models\User;

class SearchOwnerOptionListener
{
    public function handle(): array
    {
        return ['label' => __p('user::phrase.user'), 'value' => User::ENTITY_TYPE];
    }
}
