<?php

namespace MetaFox\Subscription\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Subscription\Models\SubscriptionPendingRegistrationUser;
use MetaFox\Subscription\Repositories\SubscriptionPendingRegistrationUserRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class SubscriptionPendingRegistrationUserRepository.
 */
class SubscriptionPendingRegistrationUserRepository extends AbstractRepository implements SubscriptionPendingRegistrationUserRepositoryInterface
{
    public function model()
    {
        return SubscriptionPendingRegistrationUser::class;
    }

    public function createPendingRegistrationUser(User $context, int $invoiceId): SubscriptionPendingRegistrationUser
    {
        return $this->firstOrCreate([
            'invoice_id' => $invoiceId,
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'created_at' => $this->getModel()->freshTimestamp(),
        ]);
    }

    public function deletePendingRegistrationUser(User $context, ?int $invoiceId = null): bool
    {
        $where = [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
        ];

        if (null !== $invoiceId) {
            Arr::set($where, 'invoice_id', $invoiceId);
        }

        $models = $this->getModel()->newModelQuery()
            ->where($where)
            ->get();

        if (null !== $models) {
            foreach ($models as $model) {
                $model->delete();
            }
        }

        return true;
    }

    public function getPendingRegistrationUser(User $context, ?int $invoiceId = null): ?SubscriptionPendingRegistrationUser
    {
        $query = $this->getModel()
            ->newModelQuery()
            ->where([
                'user_id'   => $context->entityId(),
                'user_type' => $context->entityType(),
            ]);

        if (null != $invoiceId) {
            $query->where('invoice_id', $invoiceId);
        }

        return $query->first();
    }
}
