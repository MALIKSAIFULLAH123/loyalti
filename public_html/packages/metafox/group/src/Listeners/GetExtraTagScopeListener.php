<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ExtraTagScope;
use MetaFox\Platform\Support\Browse\Scopes\BaseScope;

class GetExtraTagScopeListener
{
    public function handle(?Model $model): ?BaseScope
    {
        if (!$model instanceof Group) {
            return null;
        }

        if ($model->isPublicPrivacy()) {
            return null;
        }

        $extraTagScope = new ExtraTagScope();
        $extraTagScope->setGroupId($model->entityId());

        return $extraTagScope;
    }
}
