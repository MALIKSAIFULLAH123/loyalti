<?php

namespace MetaFox\Mfa\Repositories\Eloquent;

use MetaFox\Mfa\Models\EnforceRequest;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Mfa\Repositories\EnforceRequestRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class EnforceRequestRepository.
 */
class EnforceRequestRepository extends AbstractRepository implements EnforceRequestRepositoryInterface
{
    public function model()
    {
        return EnforceRequest::class;
    }

    public function createRequest(User $user, array $attributes): EnforceRequest
    {
        $attributes = array_merge([
            'user_id'     => $user->entityId(),
            'user_type'   => $user->entityType(),
            'is_active'   => 1,
            'due_at' => null, // forever
        ], $attributes);

        $request = new EnforceRequest($attributes);
        $request->save();

        return $request;
    }

    public function getActiveRequest(User $user): ?EnforceRequest
    {
        return $this->getModel()
            ->newQuery()
            ->where('user_id', $user->entityId())
            ->where('user_type', $user->entityType())
            ->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->first();
    }
}
