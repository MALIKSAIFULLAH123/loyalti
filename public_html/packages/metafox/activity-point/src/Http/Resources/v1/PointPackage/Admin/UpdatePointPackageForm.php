<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\ActivityPoint\Models\PointPackage as Model;
use MetaFox\ActivityPoint\Policies\PackagePolicy;
use MetaFox\ActivityPoint\Repositories\PointPackageRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdatePointPackageForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverType form
 * @driverName activitypoint_package.update
 */
class UpdatePointPackageForm extends StorePointPackageForm
{
    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(PointPackageRepositoryInterface $repository, ?int $package = null): void
    {
        $context = user();
        $this->resource = $repository->find($package);

        policy_authorize(PackagePolicy::class, 'update', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $values = [
            'title'     => $this->resource->title,
            'amount'    => $this->resource->amount,
            'is_active' => (int)$this->resource->is_active,
            'price'     => $this->resource->price,
        ];

        $this->title(__p('activitypoint::phrase.edit_package'))
            ->action('/admincp/activitypoint/package/' . $this->resource->entityId())
            ->asPut()
            ->setValue($values);
    }
}
