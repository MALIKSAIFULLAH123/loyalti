<?php

namespace MetaFox\Saved\Repositories\Eloquent;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\EntityTypeScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Saved\Models\Saved;
use MetaFox\Saved\Models\SavedList;
use MetaFox\Saved\Models\SavedListData;
use MetaFox\Saved\Models\SavedListItemView;
use MetaFox\Saved\Models\SavedSearchItem;
use MetaFox\Saved\Policies\SavedPolicy;
use MetaFox\Saved\Repositories\SavedListItemViewRepositoryInterface;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;
use MetaFox\Saved\Support\Browse\Scopes\Saved\OpenStatusScope;
use MetaFox\Saved\Support\Browse\Scopes\Saved\SearchScope;
use MetaFox\Saved\Support\Browse\Scopes\Saved\ViewScope;

/**
 * Class SavedRepository.
 *
 * @method Saved find($id, $columns = ['*'])
 * @method Saved getModel()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 */
class SavedRepository extends AbstractRepository implements SavedRepositoryInterface
{
    public function model(): string
    {
        return Saved::class;
    }

    protected function itemViewRepository(): SavedListItemViewRepositoryInterface
    {
        return app(SavedListItemViewRepositoryInterface::class);
    }

    public function viewSavedItems(User $context, array $attribute): Paginator
    {
        policy_authorize(SavedPolicy::class, 'viewAny', $context);

        $search      = $attribute['q'];
        $type        = $attribute['type'];
        $open        = $attribute['open'];
        $when        = $attribute['when'];
        $limit       = $attribute['limit'];
        $sortType    = $attribute['sort_type'];
        $savedListId = $attribute['collection_id'];

        $query = $this->getModel()->newQuery();

        if (!empty($search)) {
            $searchTitleScope = new SearchScope();

            $searchTitleScope->setSearchText($search)
                ->setFields(['saved_search_items.title'])
                ->setJoinedTable('saved_search_items')
                ->setJoinedField('item_id')
                ->setTableField('item_id')
                ->setAdditionalPairJoinedTableFields([['item_type', 'item_type']]);

            $query = $query->addScope($searchTitleScope);
        }

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $sortScope = new SortScope();
        $sortScope->setSort(Browse::SORT_RECENT)->setSortType($sortType);

        $openScope = new OpenStatusScope();
        $openScope->setOpenValue($open)->setUserId($context->entityId())->setSavedListId($savedListId);

        $viewScope = new ViewScope();
        $viewScope->setUserId($context->entityId())->setSavedListId($savedListId)->setItemType($type);

        $entityScope = new EntityTypeScope($this->getModel()->getTable());

        return $query
            ->with(['userEntity'])
            ->withCount(['savedLists'])
            ->addScope($viewScope)
            ->addScope($openScope)
            ->addScope($whenScope)
            ->addScope($sortScope)
            ->addScope($entityScope)
            ->simplePaginate($limit);
    }

    public function viewSavedItem(User $context, int $id): Saved
    {
        $saved = $this->with(['userEntity', 'savedLists'])->find($id);
        policy_authorize(SavedPolicy::class, 'view', $context, $saved);

        return $saved;
    }

    public function createSaved(User $context, array $attributes): Saved
    {
        policy_authorize(
            SavedPolicy::class,
            'create',
            $context,
            null,
            Arr::get($attributes, 'item_type'),
            Arr::get($attributes, 'item_id'),
            (bool) Arr::get($attributes, 'in_feed', false)
        );

        $saved = new Saved(array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ]));

        if (null == $saved->item) {
            abort(404, __p('core::phrase.this_post_is_no_longer_available'));
        }

        if ($this->checkIsSaved($saved->userId(), $saved->itemId(), $saved->itemType())) {
            throw ValidationException::withMessages([
                __p('saved::validation.this_item_has_been_saved'),
            ]);
        }

        $saved->save();

        return $saved->refresh();
    }

    public function updateSaved(User $context, int $id, array $attributes): Saved
    {
        $saved = $this->find($id);
        policy_authorize(SavedPolicy::class, 'update', $context, $saved);

        $saved->fill($attributes);
        $saved->save();

        return $saved->refresh();
    }

    public function deleteSaved(User $context, int $id): bool
    {
        $saved = $this->find($id);
        policy_authorize(SavedPolicy::class, 'delete', $context, $saved);

        return (bool) $saved->delete();
    }

    public function findSavedItem(User $context, array $attributes): Saved|null
    {
        return $this->getModel()->newQuery()
            ->where([
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
                'item_id'   => $attributes['item_id'],
                'item_type' => $attributes['item_type'],
            ])->first();
    }

    public function unSave(User $context, array $attributes): bool
    {
        /** @var Saved $saved */
        $saved = $this->getModel()->newQuery()
            ->where([
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
                'item_id'   => $attributes['item_id'],
                'item_type' => $attributes['item_type'],
            ])->firstOrFail();

        policy_authorize(SavedPolicy::class, 'delete', $context, $saved);

        return (bool) $saved->delete();
    }

    public function deleteForUser(User $user)
    {
        $savedIds = $this->getModel()->newQuery()
            ->where('user_id', $user->entityId())
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        if (!empty($savedIds)) {
            $this->getModel()->newQuery()->whereIn('id', $savedIds)->each(function (Saved $saved) {
                $saved->delete();
            });
            SavedListData::query()->whereIn('saved_id', $savedIds)->delete();
        }
    }

    public function deleteForItem(HasSavedItem $item)
    {
        $savedIds = $this->getModel()->newQuery()
            ->where('item_id', $item->entityId())
            ->where('item_type', $item->entityType())
            ->get(['id'])
            ->pluck('id')
            ->toArray();

        if (!empty($savedIds)) {
            $this->getModel()->newQuery()->whereIn('id', $savedIds)->each(function (Saved $saved) {
                $saved->delete();
            });
            SavedListData::query()->whereIn('saved_id', $savedIds)->delete();
        }

        SavedSearchItem::query()
            ->where('item_id', '=', $item->entityId())
            ->where('item_type', '=', $item->entityType())
            ->delete();
    }

    public function checkIsSaved(int $userId, int $itemId, string $itemType): bool
    {
        return $this->getModel()->newQuery()
            ->where('item_id', $itemId)
            ->where('item_type', $itemType)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * @param User  $context
     * @param int   $itemId
     * @param array $listIds
     * @return Saved
     * @throws AuthorizationException
     */
    public function addToList(User $context, int $itemId, array $listIds): Saved
    {
        $saved = $this->with(['userEntity', 'savedLists'])->find($itemId);
        policy_authorize(SavedPolicy::class, 'update', $context, $saved);

        foreach ($listIds as $listId) {
            if (!$this->isAddedToList($saved, $listId)) {
                continue;
            }
        }

        $saved->savedLists()->sync($listIds);
        $saved->savedLists()->update([
            'item_added_at' => Carbon::now(),
        ]);
        $saved->refresh();

        return $saved;
    }

    public function isAddedToList(Saved $item, int $listId): bool
    {
        $itemId = $item->entityId();

        return $item->whereHas('savedLists', function (Builder $query) use ($itemId, $listId) {
            $query->where('saved_list_data.list_id', '=', $listId)
                ->where('saved_list_data.saved_id', '=', $itemId);
        })->exists();
    }

    /**
     * @throws AuthorizationException
     */
    public function markAsOpened(User $context, int $itemId): Saved
    {
        $saved = $this->find($itemId);
        policy_authorize(SavedPolicy::class, 'update', $context, $saved);

        $saved->update(['is_opened' => 1]);
        $saved->refresh();

        return $saved;
    }

    /**
     * @throws AuthorizationException
     */
    public function markAsUnOpened(User $context, int $itemId): Saved
    {
        $saved = $this->find($itemId);
        policy_authorize(SavedPolicy::class, 'update', $context, $saved);

        $saved->update(['is_opened' => 0]);
        $saved->refresh();

        return $saved;
    }

    public function getAvailableTypes(): array
    {
        return $this->getModel()->newQuery()
            ->groupBy('item_type')
            ->get(['item_type'])
            ->pluck('item_type')
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getCollectionByItem(int $itemId): Saved
    {
        return $this->find($itemId);
    }

    public function getFilterOptions(): array
    {
        $savedItemType        = [
            [
                'label' => __p('core::phrase.all'),
                'value' => 'all',
            ],
        ];
        $moduleSavedItemTypes = PackageManager::discoverSettings('getSavedTypes');

        if (empty($moduleSavedItemTypes)) {
            return $savedItemType;
        }

        foreach ($moduleSavedItemTypes as $values) {
            foreach ($values as $value) {
                $savedItemType[] = $value;
            }
        }

        return collect($savedItemType)->sortBy(['label', 'asc'])->values()->toArray();
    }

    public function removeCollectionItem(User $context, array $attributes): mixed
    {
        $collection = SavedList::query()->getModel()
            ->find($attributes['collection_id']);
        $saved      = $this->find($attributes['saved_id']);

        policy_authorize(SavedPolicy::class, 'removeItemFromCollection', $context, $collection, $saved);

        return SavedListData::query()->getModel()
            ->where([
                'list_id'  => $attributes['collection_id'],
                'saved_id' => $attributes['saved_id'],
            ])
            ->delete();
    }

    public function buildQuerySavedItemUnopened(User $context, Saved $saved): Builder
    {
        $query = $this->getModel()->newQuery();

        $listViewQuery = SavedListItemView::query()->where('saved_id', $saved->entityId())
            ->where('user_id', $context->entityId())
            ->select('list_id');

        $query->rightJoin('saved_list_data as list', 'list.saved_id', '=', 'saved_items.id')
            ->where('saved_items.id', $saved->entityId());

        $query->whereNotIn('list.list_id', $listViewQuery);

        return $query;
    }
}
