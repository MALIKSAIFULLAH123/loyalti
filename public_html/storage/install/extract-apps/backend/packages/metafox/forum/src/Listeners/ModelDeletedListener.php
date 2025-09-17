<?php

namespace MetaFox\Forum\Listeners;

use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;

class ModelDeletedListener
{
    public function handle($model): void
    {
        if (!($model instanceof Entity)) {
            return;
        }

        $thread = resolve(ForumThreadRepositoryInterface::class)->getModel()
            ->newQuery()
            ->where([
                'item_type' => $model->entityType(),
                'item_id'   => $model->entityId(),
            ])
            ->first();

        if (!$thread) {
            return;
        }

        $thread->update([
            'item_type' => null,
            'item_id'   => 0,
        ]);
    }
}
