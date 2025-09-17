<?php

namespace MetaFox\EMoney\Listeners;

use MetaFox\EMoney\Models\Transaction;
use MetaFox\EMoney\Repositories\TransactionRepositoryInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

class CreateTransactionListener
{
    public function handle(User $user, User $owner, Entity $entity, string $currency, float $total, ?float $commissionPercentage = null, ?int $holdingDays = null, ?string $target = null): ?Transaction
    {
        return $this->getRepository()->createTransactionForIntegration($user, $owner, $entity, $currency, $total, $commissionPercentage, $holdingDays, $target);
    }

    protected function getRepository(): TransactionRepositoryInterface
    {
        return resolve(TransactionRepositoryInterface::class);
    }
}
