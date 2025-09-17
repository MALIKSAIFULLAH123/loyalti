<?php

namespace MetaFox\Search\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Search\Models\Search;
use MetaFox\Search\Repositories\SearchRepositoryInterface;

/**
 * Class ModelUpdatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelUpdatedListener
{
    public function __construct(protected SearchRepositoryInterface $repository) { }

    /**
     * @param mixed $model
     */
    public function handle($model): void
    {
        if (!$model instanceof HasGlobalSearch) {
            return;
        }

        if (!$model->isApproved()) {
            $this->deletedBy($model);
            return;
        }

        $this->repository->updatedBy($model);
    }

    public function deletedBy(HasGlobalSearch $item): void
    {
        // validate is item
        if (!$item instanceof Content) {
            return;
        }

        $coreData = [
            'item_id'    => $item->entityId(),
            'item_type'  => $item->entityType(),
            'user_id'    => $item->userId(),
            'user_type'  => $item->userType(),
            'owner_id'   => $item->ownerId(),
            'owner_type' => $item->ownerType(),
        ];

        $model = $this->repository->getModel()->newQuery()
            ->where($coreData)
            ->first();

        if (!$model instanceof Search) {
            return;
        }

        $model->delete();
    }
}
