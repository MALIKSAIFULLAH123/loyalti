<?php

namespace MetaFox\Advertise\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Support\Support;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\Driver;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class SponsorPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SponsorPolicy implements ResourcePolicyInterface
{
    use HandlesAuthorization;
    use HasPolicyTrait;

    protected string $type = 'advertise_sponsor';

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($this->hasSuperAdminRole($user)) {
            return true;
        }

        if ($user->isGuest()) {
            return false;
        }

        return true;
    }

    public function viewAdminCP(User $user): bool
    {
        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return false;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if (null === $owner) {
            return false;
        }

        if ($this->hasSuperAdminRole($user)) {
            return true;
        }

        if (!$this->hasViewPermission($user)) {
            return false;
        }

        if ($user->entityId() == $owner->entityId()) {
            return true;
        }

        return false;
    }

    public function view(User $user, Entity $resource): bool
    {
        if ($this->hasSuperAdminRole($user)) {
            return true;
        }

        if (!$this->hasViewPermission($user)) {
            return false;
        }

        $owner = $resource->user;

        if (!$owner instanceof User) {
            return false;
        }

        if ($owner->entityId() == $user->entityId()) {
            return true;
        }

        return false;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function purchaseSponsor(User $user, ?Entity $resource): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $this->type, 'create'))) {
            return false;
        }

        if (!$this->isResourceActive($resource)) {
            return false;
        }

        if ($user->can('purchaseSponsor', [$resource])) {
            return true;
        }

        return false;
    }

    public function purchaseSponsorInFeed(User $user, ?Entity $resource): bool
    {
        if (!$resource instanceof Content) {
            return false;
        }

        if (!$user->hasPermissionTo(sprintf('%s.%s', $this->type, 'create'))) {
            return false;
        }

        if (!$this->isResourceActive($resource)) {
            return false;
        }

        if ($user->can('purchaseSponsorInFeed', [$resource])) {
            return true;
        }

        return false;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($this->viewAdminCP($user)) {
            return true;
        }

        if (!$user->hasPermissionTo('advertise_sponsor.update')) {
            return false;
        }

        if ($resource instanceof Content) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function active(User $user, Entity $resource): bool
    {
        return $this->update($user, $resource);
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($this->viewAdminCP($user)) {
            return true;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('advertise_sponsor.delete')) {
            return false;
        }

        if ($resource instanceof Entity) {
            if ($user->entityId() != $resource->userId()) {
                return false;
            }
        }

        return true;
    }

    public function payment(User $user, Entity $entity): bool
    {
        if (!$entity->item instanceof Content) {
            return false;
        }

        if ($entity->status != Support::ADVERTISE_STATUS_UNPAID) {
            return false;
        }

        if ($entity->sponsor_type == Support::SPONSOR_TYPE_FEED) {
            return $this->purchaseSponsorInFeed($user, $entity->item->item);
        }

        return $this->purchaseSponsor($user, $entity->item);
    }

    protected function hasSuperAdminRole(User $user): bool
    {
        return $user->hasSuperAdminRole();
    }

    protected function hasViewPermission(User $user): bool
    {
        return $user->hasPermissionTo(sprintf('%s.%s', $this->type, 'view'));
    }

    public function approve(User $user, Entity $entity): bool
    {
        /**
         * @var Sponsor $entity
         */
        if (!$entity->is_pending && !$entity->is_denied) {
            return false;
        }

        if ($this->viewAdminCP($user)) {
            return true;
        }

        return false;
    }

    public function deny(User $user, Entity $entity): bool
    {
        /**
         * @var Sponsor $entity
         */
        if (!$entity->is_pending) {
            return false;
        }

        if ($this->viewAdminCP($user)) {
            return true;
        }

        return false;
    }

    public function markAsPaid(User $user, Entity $entity): bool
    {
        /**
         * @var Sponsor $entity
         */
        if (!$entity->is_unpaid) {
            return false;
        }

        if ($this->viewAdminCP($user)) {
            return true;
        }

        return false;
    }

    private function isResourceActive(?Entity $entity): bool
    {
        if (null === $entity) {
            return false;
        }

        $packageId = $this->resolvePackageIdFromEntityType($entity->entityType());

        if (null === $packageId) {
            return false;
        }

        if (!app_active($packageId)) {
            return false;
        }

        return true;
    }

    private function resolvePackageIdFromEntityType(string $entityType): ?string
    {
        $driverRepository = resolve(DriverRepositoryInterface::class);

        /**
         * @todo: Remove if after released with 5.1.8.0
         */
        if (method_exists($driverRepository, 'getPackageIdByEntityType')) {
            return call_user_func([$driverRepository, 'getPackageIdByEntityType'], $entityType);
        }

        return LoadReduce::get(sprintf('advertise::sponsor_policy::resolvePackageIdFromEntityType(%s)', $entityType), function () use ($entityType) {
            /**
             * @var Driver $driver
             */
            $driver = Driver::query()
                ->where([
                    'type' => Constants::DRIVER_TYPE_ENTITY,
                    'name' => $entityType
                ])
                ->first();

            if (null === $driver) {
                return null;
            }

            return $driver->package_id;
        });
    }
}
