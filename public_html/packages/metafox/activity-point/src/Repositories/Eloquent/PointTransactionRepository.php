<?php

namespace MetaFox\ActivityPoint\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Models\ActionType;
use MetaFox\ActivityPoint\Models\PointTransaction;
use MetaFox\ActivityPoint\Models\PointTransaction as Model;
use MetaFox\ActivityPoint\Repositories\PointTransactionRepositoryInterface;
use MetaFox\ActivityPoint\Support\ActivityPoint as PointSupport;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;
use MetaFox\Platform\Support\Browse\Scopes\RelationSearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class PointTransactionRepository.
 *
 * @method Model find($id, $columns = ['*'])
 * @method Model getModel()
 */
class PointTransactionRepository extends AbstractRepository implements PointTransactionRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function viewTransactions(User $context, array $attributes): Paginator
    {
        $type     = Arr::get($attributes, 'type', 0);
        $dateFrom = Arr::get($attributes, 'from');
        $dateTo   = Arr::get($attributes, 'to');
        $sort     = Arr::get($attributes, 'sort');
        $sortType = Arr::get($attributes, 'sort_type');

        $query = $this->getModel()->newModelQuery();

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $packageScope = new PackageScope($this->getModel()->getTable());

        if ($type > 0) {
            $query->where('type', '=', $type);
        }

        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<=', $dateTo);
        }

        return $query
            ->with(['user', 'owner'])
            ->addScope($sortScope)
            ->addScope($packageScope)
            ->where('is_hidden', '=', 0)
            ->where('points', '<>', 0)
            ->where('user_id', '=', $context->entityId())
            ->paginate($attributes['limit']);
    }

    /**
     * @param  User       $user
     * @param  Entity     $model
     * @param  array      $attributes
     * @return Model|null
     */
    public function getTransactionByItem(User $user, Entity $model, array $attributes): ?Model
    {
        $pointSettingId = Arr::get($attributes, 'point_setting_id');

        $type           = Arr::get($attributes, 'type') ?? PointSupport::TYPE_EARNED;

        $whereData      = [
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
            'item_id'   => $model->entityId(),
            'item_type' => $model->entityType(),
            'type'      => $type,
        ];

        if ($pointSettingId) {
            Arr::set($whereData, 'point_setting_id', $pointSettingId);
        }

        $transaction = $this->getModel()
            ->newModelQuery()
            ->where($whereData)
            ->first();

        if (!$transaction instanceof Model) {
            return null;
        }

        return $transaction;
    }

    /**
     * @inheritDoc
     */
    public function viewTransaction(User $context, int $id): Model
    {
        $transaction = $this->getModel()->newModelQuery()
            ->with([
                'user',
                'owner',
                'userEntity',
                'ownerEntity',
            ])
            ->where('user_id', '=', $context->entityId())
            ->firstOrFail();

        if (!$transaction instanceof Model) {
            abort(404, 'Not Found');
        }

        return $transaction;
    }

    /**
     * @inheritDoc
     */
    public function createTransaction(User $context, User $owner, array $params): Model
    {
        $attributes = array_merge([
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
            'package_id' => 'metafox/activity-point',
        ], $params);

        $actionId = $this->getActionIdByName(Arr::get($attributes, 'action_type_name'));
        if ($actionId) {
            $attributes['action_id'] = $actionId;
        }

        $transaction = new Model();
        $transaction->fill($attributes);
        $transaction->save();

        return $transaction;
    }

    private function getActionIdByName(?string $name): ?int
    {
        return ActionType::query()->where('name', $name)->first()?->id;
    }

    /**
     * @inheritDoc
     */
    public function viewTransactionsAdmin(User $context, array $attributes): Paginator
    {
        $type        = Arr::get($attributes, 'type', 0);
        $dateFrom    = Arr::get($attributes, 'from');
        $dateTo      = Arr::get($attributes, 'to');
        $sort        = Arr::get($attributes, 'sort');
        $sortType    = Arr::get($attributes, 'sort_type');
        $search      = Arr::get($attributes, 'q');
        $packageId   = Arr::get($attributes, 'package_id');
        $actionId    = Arr::get($attributes, 'action_id');
        $userId      = Arr::get($attributes, 'user_id');
        $limit       = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->getModel()->newModelQuery();

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $packageScope = new PackageScope($this->getModel()->getTable());

        if ($search) {
            $searchScope = new RelationSearchScope();
            $searchScope->setTable('users')
                ->setSearchText($search)
                ->setRelation('user')
                ->setFields(['full_name']);
            $query = $query->addScope($searchScope);
        }

        if ($type > 0) {
            $query->where('type', '=', $type);
        }

        if ($dateFrom) {
            $query->where('created_at', '>', $dateFrom);
        }

        if ($dateTo) {
            $query->where('created_at', '<', $dateTo);
        }

        if ($packageId != 'all') {
            $query->where('package_id', '=', $packageId);
        }

        if ($actionId) {
            $query->where('action_id', '=', $actionId);
        }

        if ($userId) {
            $query->where('user_id', '=', $userId);
        }

        return $query
            ->with(['user', 'owner'])
            ->addScope($sortScope)
            ->addScope($packageScope)
            ->where('is_hidden', '=', 0)
            ->paginate($limit);
    }

    public function getPackageOptions(): array
    {
        $result       = [];
        $packageScope = new PackageScope(resolve(PointTransaction::class)->getTable());

        $packageIds  = PointTransaction::query()
            ->addScope($packageScope)
            ->pluck('package_id')
            ->unique()
            ->toArray();

        foreach ($packageIds as  $packageId) {
            $alias = PackageManager::getAlias($packageId);

            $result[] = [
                'label' => __p("$alias::phrase.$alias"),
                'value' => $packageId,
            ];
        }

        return collect($result)->sortBy('label')->toArray();
    }

    public function getAdminSentPointByTime(string $time): int
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('is_admincp', 1)
            ->where('created_at', '>=', $time)
            ->sum('points');
    }
}
