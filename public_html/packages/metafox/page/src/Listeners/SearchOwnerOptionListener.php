<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Models\Page;

class SearchOwnerOptionListener
{
    public function handle(): array
    {
        return ['label' => __p('page::phrase.page'), 'value' => Page::ENTITY_TYPE];
    }
}
