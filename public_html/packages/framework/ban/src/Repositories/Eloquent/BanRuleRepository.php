<?php

namespace MetaFox\Ban\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Ban\Models\BanRule;
use MetaFox\Ban\Repositories\BanRuleRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class BanRuleRepository.
 */
class BanRuleRepository extends AbstractRepository implements BanRuleRepositoryInterface
{
    public function model(): string
    {
        return BanRule::class;
    }

    public function createBanRule(User $context, array $attributes): BanRule
    {
        $attributes = [
            'user_id'             => $context->entityId(),
            'user_type'           => $context->entityType(),
            'type_id'             => Arr::get($attributes, 'type'),
            'find_value'          => Arr::get($attributes, 'find_value'),
            'replacement'         => Arr::get($attributes, 'replacement'),
            'ban_user'            => (int) Arr::get($attributes, 'is_ban_user', 0),
            'day_banned'          => (int) Arr::get($attributes, 'day', 0),
            'return_user_group'   => (int) Arr::get($attributes, 'return_user_group'),
            'reason'              => Arr::get($attributes, 'reason'),
            'user_group_effected' => Arr::get($attributes, 'user_group_effected'),
        ];

        return $this->getModel()->newQuery()->create($attributes);
    }

    public function viewBanRule(User $context, array $attributes): Paginator
    {
        $limit  = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);
        $type   = Arr::get($attributes, 'type');
        $search = Arr::get($attributes, 'q');

        $query = $this->getModel()
            ->newQuery()
            ->where('type_id', $type);

        if ($search) {
            $query = $query->addScope(new SearchScope($search, ['find_value', 'replacement']));
        }

        return $query
            ->orderByDesc('id')
            ->paginate($limit);
    }

    public function isExistBanRule(mixed $findValue, string $type): bool
    {
        return $this->getModel()
            ->newQuery()
            ->where([
                'find_value' => $findValue,
                'type_id'    => $type,
            ])->exists();
    }

    public function deleteBanRule(int $id): bool
    {
        return (bool) $this->find($id)->delete();
    }

    public function getBanRulesByType(string $type): Collection
    {
        return $this->getModel()
            ->newQuery()
            ->where('type_id', $type)
            ->where('is_active', true)
            ->get();
    }
}
