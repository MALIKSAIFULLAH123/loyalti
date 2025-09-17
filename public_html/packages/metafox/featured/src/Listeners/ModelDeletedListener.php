<?php
namespace MetaFox\Featured\Listeners;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Content;

class ModelDeletedListener
{
    public function handle(Entity $entity): void
    {
        if (!$entity instanceof Content) {
            return;
        }

        if (!is_array($entity->toFeaturedData())) {
            return;
        }

        $this->handleContentDeleted($entity);
    }

    protected function handleContentDeleted(Content $content): void
    {
        Feature::handleContentDeleted($content);
    }
}
