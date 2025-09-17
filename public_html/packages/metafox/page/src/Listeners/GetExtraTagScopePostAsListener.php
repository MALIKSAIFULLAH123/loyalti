<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Support\Browse\Scopes\PageMember\ExtraTagScope;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class GetExtraTagScopePostAsListener
{
    public function handle(?Model $model): ?BaseScope
    {
        if (!$model instanceof Page) {
            return null;
        }

        $extraTagScope = new ExtraTagScope();
        $extraTagScope->setPageId($model->entityId());

        return $extraTagScope;
    }
}
