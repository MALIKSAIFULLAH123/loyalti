<?php

namespace MetaFox\Photo\Listeners;

use MetaFox\Photo\Models\PhotoGroup;
use MetaFox\Photo\Support\Traits\HandlePhotoGroupItemStatisticTrait;
use MetaFox\Platform\Contracts\HasAmounts;

class UpdateTotalStatisticListener
{
    use HandlePhotoGroupItemStatisticTrait;

    public function handle(HasAmounts $model): void
    {
        if (!$model instanceof PhotoGroup) {
            return;
        }

        $this->incrementSingleItemStatInPhotoGroup($model);
    }
}
