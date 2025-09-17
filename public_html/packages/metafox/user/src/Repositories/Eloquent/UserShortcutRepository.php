<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasShortcutItem;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\User\Models\UserEntity;
use MetaFox\User\Models\UserShortcut;
use MetaFox\User\Policies\UserShortcutPolicy;
use MetaFox\User\Repositories\UserShortcutRepositoryInterface;

/**
 * @method UserShortcut getModel()
 * @method UserShortcut find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserShortcutRepository extends AbstractRepository implements UserShortcutRepositoryInterface
{
    public function model()
    {
        return UserShortcut::class;
    }

    protected function buildShortcutQuery(ContractUser $context): Builder
    {
        $shortcutTypes = app('events')->dispatch('user.get_shortcut_type');
        $query         = $this->getModel()->newModelQuery();

        if (!empty($shortcutTypes)) {
            foreach ($shortcutTypes as $key => $shortcut) {
                if ($shortcut == null) {
                    unset($shortcutTypes[$key]);
                    continue;
                }

                if (!$context->hasPermissionTo($shortcut . '.view')) {
                    unset($shortcutTypes[$key]);
                }
            }
        }

        $query->join('user_entities', 'user_entities.id', '=', 'user_shortcuts.item_id')
            ->whereIn('user_entities.entity_type', Arr::wrap($shortcutTypes))
            ->where('user_shortcuts.user_id', $context->entityId())
            ->orderByDesc('user_shortcuts.sort_type')
            ->orderByDesc('user_shortcuts.id');

        return $query;
    }

    public function viewShortcuts(ContractUser $context, array $attributes): Collection
    {
        $hide = UserShortcut::SORT_HIDE;
        policy_authorize(UserShortcutPolicy::class, 'viewAny', $context);

        $query = $this->buildShortcutQuery($context);

        $query->whereNot('user_shortcuts.sort_type', $hide);

        $paginatorData = $query->paginate($attributes['limit'] ?? 10);
        $data          = $paginatorData->items();

        return $this->toUserEntities($data);
    }

    /**
     * @param array<mixed> $collection
     *
     * @return Collection
     */
    private function toUserEntities(array $collection): Collection
    {
        $entities = [];

        foreach ($collection as $item) {
            $json = json_encode($item);
            if ($json == false) {
                continue;
            }
            $data   = json_decode($json, true);
            $entity = new UserEntity();
            $entity->append(['sort_type']);
            $entity->fill($data);
            $entity->sort_type = $data['sort_type'];
            $entities[]        = $entity;
        }

        return new Collection($entities);
    }

    public function viewForEdit(ContractUser $context, array $attributes): Collection
    {
        policy_authorize(UserShortcutPolicy::class, 'viewAny', $context);

        $limit  = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $search = Arr::get($attributes, 'q');
        $query  = $this->buildShortcutQuery($context);

        if ($search) {
            $query->where('user_entities.name', $this->likeOperator(), '%' . $search . '%');
            $query->orWhere('user_entities.user_name', $this->likeOperator(), '%' . $search . '%');
        }

        $paginatorData = $query->paginate($limit);
        $data          = $paginatorData->items();

        return $this->toUserEntities($data);
    }

    public function updateShortType(ContractUser $context, Content $content, int $sortType): UserShortcut
    {
        policy_authorize(UserShortcutPolicy::class, 'moderate', $context);

        /** @var UserShortcut $userShortcut */
        $userShortcut = $this->getModel()->newQuery()->updateOrCreate([
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'item_id'   => $content->entityId(),
            'item_type' => $content->entityType(),
        ], [
            'sort_type'  => $sortType,
            'updated_at' => Carbon::now(),
        ]);

        return $userShortcut;
    }

    /**
     * @inheritDoc
     */
    public function createdBy(HasShortcutItem $item): void
    {
        $data = $item->toShortcutItem();

        if (!$data) {
            return;
        }

        $this->getModel()->newModelQuery()->firstOrCreate($data);
    }

    /**
     * @inheritDoc
     */
    public function deletedBy(HasShortcutItem $item): void
    {
        $data = $item->toShortcutItem();

        if (!$data) {
            return;
        }

        $this->getModel()->newModelQuery()->where($data)->delete();
    }

    /**
     * @inheritDoc
     */
    public function deletedByItem(ContractUser $item): void
    {
        if ($item->isDeleted()) {
            $this->getModel()->newModelQuery()
                ->where('item_id', $item->entityId())
                ->where('item_type', $item->entityType())->delete();
        }
    }
}
