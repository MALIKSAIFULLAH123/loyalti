<?php
namespace MetaFox\Featured\Traits\Package;

use MetaFox\Featured\Models\Package;
use MetaFox\Featured\Policies\PackagePolicy;
use MetaFox\Platform\Facades\PolicyGate;

/**
 * @property Package $resource
 */
trait ExtraTrait
{
    public function getExtra(): array
    {
        /**
         * @var PackagePolicy $policy
         */
        $policy = PolicyGate::getPolicyFor(Package::class);

        if (null === $policy) {
            return [];
        }

        $context = user();

        return [
            'can_edit' => $policy->edit($context, $this->resource),
            'can_delete' => $policy->delete($context, $this->resource),
        ];
    }
}
