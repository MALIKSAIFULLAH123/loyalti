<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;

class GetRoleSubscriptionPackageByIdListener
{
    public function __construct(protected SubscriptionPackageRepositoryInterface $packageRepository) { }

    public function handle(int $id): ?int
    {
        $package = $this->packageRepository->find($id);

        return $package?->upgraded_role_id;
    }
}
