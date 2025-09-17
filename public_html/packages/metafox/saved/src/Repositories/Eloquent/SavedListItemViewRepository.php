<?php

namespace MetaFox\Saved\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedListItemView;
use MetaFox\Saved\Policies\SavedListPolicy;
use MetaFox\Saved\Policies\SavedPolicy;
use MetaFox\Saved\Repositories\SavedListItemViewRepositoryInterface;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SavedListItemViewRepository.
 *
 * @method SavedListItemView find($id, $columns = ['*'])
 * @method SavedListItemView getModel()
 */
class SavedListItemViewRepository extends AbstractRepository implements SavedListItemViewRepositoryInterface
{
    public function model()
    {
        return SavedListItemView::class;
    }

    /**
     * @return SavedRepositoryInterface
     */
    protected function savedRepository(): SavedRepositoryInterface
    {
        return resolve(SavedRepositoryInterface::class);
    }

    /**
     * @return SavedListRepositoryInterface
     */
    protected function savedListRepository(): SavedListRepositoryInterface
    {
        return resolve(SavedListRepositoryInterface::class);
    }

    /**
     * @throws AuthorizationException
     */
    public function markAsOpened(User $context, array $attributes): Saved
    {
        $savedId = Arr::get($attributes, 'saved_id');
        $listId  = Arr::get($attributes, 'collection_id');
        $saved   = $this->savedRepository()->find($savedId);
        switch ($listId !== null) {
            case true:
                $savedList = $this->savedListRepository()->find($listId);

                policy_authorize(SavedListPolicy::class, 'viewMember', $context, $savedList);
                break;
            default:
                policy_authorize(SavedPolicy::class, 'update', $context, $saved);
        }

        $data = [
            'list_id'  => $listId,
            'saved_id' => $savedId,
            'user_id'  => $context->entityId(),
        ];

        $this->getModel()->fill($data)->save();

        return $saved;
    }

    /**
     * @throws AuthorizationException
     */
    public function markAsUnOpened(User $context, array $attributes): Saved
    {
        $savedId = Arr::get($attributes, 'saved_id');
        $listId  = Arr::get($attributes, 'collection_id');
        $saved   = $this->savedRepository()->find($savedId);

        switch ($listId !== null) {
            case true:
                $savedList = $this->savedListRepository()->find($listId);

                policy_authorize(SavedListPolicy::class, 'viewMember', $context, $savedList);
                break;
            default:
                policy_authorize(SavedPolicy::class, 'update', $context, $saved);
        }

        $data = [
            'list_id'  => $listId,
            'saved_id' => $savedId,
            'user_id'  => $context->entityId(),
        ];

        $this->getModel()->newQuery()->where($data)->delete();

        return $saved;
    }

    public function isExists(User $context, array $attributes): bool
    {
        $savedId = Arr::get($attributes, 'saved_id');
        $listId  = Arr::get($attributes, 'collection_id');
        $data    = [
            'list_id'  => $listId,
            'saved_id' => $savedId,
            'user_id'  => $context->entityId(),
        ];

        return $this->getModel()->newQuery()->where($data)->exists();
    }
}
