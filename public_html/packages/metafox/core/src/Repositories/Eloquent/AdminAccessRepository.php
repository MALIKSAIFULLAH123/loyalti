<?php

namespace MetaFox\Core\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Core\Repositories\AdminAccessRepositoryInterface;
use MetaFox\Core\Models\AdminAccess as Model;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class AdminAccessRepository.
 * @method Model getModel();
 * @method Model find($id, $columns = ['*'])
 */
class AdminAccessRepository extends AbstractRepository implements AdminAccessRepositoryInterface
{
    public function model(): string
    {
        return Model::class;
    }

    /**
     * @inheritDoc
     */
    public function logAccess(User $user, array $attributes = []): Model
    {
        /** @var Model $access */
        $access = $this->getModel()->newModelQuery()->firstOrNew([
            'user_id'   => $user->entityId(),
            'user_type' => $user->entityType(),
        ]);
        $access->fill([
            'ip_address' => Arr::get($attributes, 'ip') ?? '0.0.0.0',
            'updated_at' => Carbon::now(),
        ]);
        $access->save();

        return $access->refresh();
    }

    /**
     * @inheritDoc
     */
    public function getLatestAccesses(int $limit): Paginator
    {
        return $this->getModel()->newModelQuery()
            ->with(['user'])
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($limit);
    }

    /**
     * @inheritDoc
     */
    public function getActiveUsers(User $context, int $limit): Paginator
    {
        return $this->getModel()->newModelQuery()
            ->with(['user'])
            ->where('updated_at', '>', Carbon::now()->subMinutes(Model::USER_ACTIVE_LIMIT_IN_MINUTES))
            ->paginate($limit);
    }
}
