<?php
namespace MetaFox\Event\Listeners;

use MetaFox\Event\Models\Event;

class MigrateSuggestionSearchableListener
{
    public function handle(): array
    {
        return [
            Event::ENTITY_TYPE => false,
        ];
    }
}
