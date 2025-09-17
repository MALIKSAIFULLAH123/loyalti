<?php
namespace MetaFox\Featured\Observers;

use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Models\Item;

class ItemObserver
{
    public function deleted(Item $item): void
    {
        Feature::handleItemDeleted($item);
    }
}
