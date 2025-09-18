<?php

namespace MetaFox\Page\Listeners;

use MetaFox\Page\Models\Page as Model;

/**
 * Class GetShortcutTypeListener.
 * @ignore
 */
class GetShortcutTypeListener
{
    public function handle(): ?string
    {
        return Model::ENTITY_TYPE;
    }
}
