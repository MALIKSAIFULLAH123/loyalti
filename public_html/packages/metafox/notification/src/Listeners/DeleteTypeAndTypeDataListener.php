<?php

namespace MetaFox\Notification\Listeners;

use MetaFox\Notification\Support\TypeManager;

class DeleteTypeAndTypeDataListener
{
    public function handle(array $types): void
    {
        resolve(TypeManager::class)->handleDeletedTypeByName($types);
    }
}
