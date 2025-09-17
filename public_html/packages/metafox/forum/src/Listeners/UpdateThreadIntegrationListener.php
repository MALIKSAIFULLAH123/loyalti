<?php

namespace MetaFox\Forum\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Forum\Models\ForumThread;
use MetaFox\Forum\Repositories\ForumThreadRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;

class UpdateThreadIntegrationListener
{
    public function __construct(protected ForumThreadRepositoryInterface $repository)
    {
    }

    /**
     * @param  Model $model
     * @return void
     */
    public function handle(Model $model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        if ($model->entityType() != 'poll') {
            return;
        }

        $thread = $this->repository->getModel()->newQuery()
            ->where('item_id', $model->entityId())
            ->where('item_type', $model->entityType())
            ->first();

        if (!$thread instanceof ForumThread) {
            return;
        }

        $thread->update([
            'item_type' => null,
            'item_id'   => 0,
        ]);
    }
}
