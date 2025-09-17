<?php

namespace MetaFox\Group\Listeners;

use MetaFox\Group\Models\Group as Model;

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
