<?php

namespace MetaFox\Like\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use MetaFox\Like\Models\Reaction;
use MetaFox\Like\Policies\ReactionPolicy;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Class ReactionRepository.
 * @method Reaction getModel()
 * @method Reaction find($id, $columns = ['*'])
 * @ignore
 * @codeCoverageIgnore
 */
class ReactionRepository extends AbstractRepository implements ReactionRepositoryInterface
{
    public function model(): string
    {
        return Reaction::class;
    }

    public function viewReactionsForAdmin(User $context, array $attributes): Paginator
    {
        policy_authorize(ReactionPolicy::class, 'viewAny', $context);

        $limit = $attributes['limit'];

        return $this->getModel()->newQuery()
            ->orderBy('ordering')
            ->simplePaginate($limit);
    }

    public function viewReactionsForFE(User $context): Collection
    {
        policy_authorize(ReactionPolicy::class, 'viewAny', $context);

        return $this->getModel()->newQuery()
            ->where('is_active', Reaction::IS_ACTIVE)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    public function viewReaction(User $context, int $id): Reaction
    {
        policy_authorize(ReactionPolicy::class, 'view', $context);

        return $this->find($id);
    }

    /**
     * @inheritDoc
     */
    public function getReactionsForConfig(int $isActive = 1): Collection
    {
        return $this->getModel()
            ->newModelQuery()
            ->where('is_active', '=', $isActive)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }
}
