<?php

namespace MetaFox\Subscription\Listeners;

use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage;
use Illuminate\Support\Collection;

class SubscriptionPackageRegistrationListener
{
    public function __construct(protected SubscriptionPackageRepositoryInterface $packageRepository) { }

    public function handle(): Collection
    {
        if (!SubscriptionPackage::allowUsingPackages()) {
            return new Collection([]);
        }

        $context  = user();
        $packages = SubscriptionPackage::getPackagesForRegistration(true);

        return $this->packageRepository->filterPackagesByCurrencyId($context, $packages);
    }
}
