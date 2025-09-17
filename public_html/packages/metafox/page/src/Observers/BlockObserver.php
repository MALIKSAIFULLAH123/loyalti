<?php

namespace MetaFox\Page\Observers;

use MetaFox\Page\Models\Block as Model;

class BlockObserver
{
    public function created(Model $model): void {}
}
