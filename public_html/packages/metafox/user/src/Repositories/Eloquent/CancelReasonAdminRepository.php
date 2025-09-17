<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\User\Models\CancelReason as Model;
use MetaFox\User\Repositories\CancelReasonAdminRepositoryInterface;

/**
 * * @property Model $model
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 */
class CancelReasonAdminRepository extends AbstractRepository implements CancelReasonAdminRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @param User $context
     *
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getReasonsForForm(User $context): Collection
    {
        return $this->getModel()
            ->newModelQuery()
            ->orderBy('ordering')
            ->where('is_active', 1)
            ->get()
            ->collect();
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewReasons(User $context, array $attributes = []): Paginator
    {
        $search   = Arr::get($attributes, 'q');
        $isActive = Arr::get($attributes, 'is_active');
        $limit    = Arr::get($attributes, 'limit');

        $query = $this->getModel()->newModelQuery()->select('user_delete_reasons.*');

        $defaultLocale = Language::getDefaultLocaleId();
        if ($search) {
            $searchScope = new SearchScope($search, ['ps.text']);
            $searchScope->setTableField('phrase_var');
            $searchScope->setJoinedTable('phrases');
            $searchScope->setAliasJoinedTable('ps');
            $searchScope->setJoinedField('key');
            $query->where('ps.locale', '=', $defaultLocale);
            $query = $query->addScope($searchScope);
        }

        if (null !== $isActive) {
            $query->where('is_active', '=', $isActive ? 1 : 0);
        }

        return $query
            ->orderBy('user_delete_reasons.ordering')
            ->paginate($limit);
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function orderReasons(User $context, array $ids = []): bool
    {
        foreach ($ids as $ordering => $id) {
            $this->getModel()->newModelQuery()->where('id', $id)->update(['ordering' => $ordering]);
        }

        return true;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteReason(User $context, int $id): bool
    {
        $resource = $this->find($id);

        return (bool) $resource->delete();
    }

    /**
     * @inheritDoc
     */
    public function createReason(User $context, array $attributes = []): Model
    {
        $maxOrdering = (int) $this->getModel()->newModelQuery()->max('ordering');
        $reasonParam = array_merge($attributes, [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $context->entityId(),
            'owner_type' => $context->entityType(),
            'ordering'   => $maxOrdering + 1,
        ]);
        $reason      = new Model();
        $reason->fill($reasonParam);
        $reason->save();

        return $reason;
    }

    /**
     * @inheritDoc
     */
    public function updateReason(User $context, int $id, array $attributes = []): Model
    {
        $reason = $this->find($id);

        $updateData = Arr::only($attributes, ['phrase_var']);
        $reason->fill($updateData);
        $reason->save();

        return $reason->refresh();
    }
}
