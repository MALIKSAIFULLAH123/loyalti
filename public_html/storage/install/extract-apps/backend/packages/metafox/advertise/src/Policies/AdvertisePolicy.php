<?php

namespace MetaFox\Advertise\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Models\Placement;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Advertise\Support\Support;
use MetaFox\Authorization\Repositories\Eloquent\RoleRepository;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

/**
 * stub: /packages/policies/model_policy.stub.
 */

/**
 * Class AdvertisePolicy.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class AdvertisePolicy implements ResourcePolicyInterface
{
    use HandlesAuthorization;

    protected string $type = 'advertise';

    protected function hasAdminCPAccess(User $user): bool
    {
        if ($user->hasPermissionTo('admincp.has_admin_access')) {
            return true;
        }

        return false;
    }

    public function createAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function updateAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function deleteAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    public function viewAdminCP(User $user): bool
    {
        return $this->hasAdminCPAccess($user);
    }

    protected function canView(User $user, ?Entity $resource = null): bool
    {
        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if ($resource instanceof Advertise && $resource->is_pending) {
            return $this->viewPending($user, $resource);
        }

        return true;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        return $this->canView($user);
    }

    public function viewReport(User $user, Entity $resource): bool
    {
        if (!$resource->is_approved && !$resource->is_completed) {
            return false;
        }

        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        return false;
    }

    public function viewPending(User $user, Entity $resource): bool
    {
        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if ($user->entityId() == $resource->userId()) {
            return true;
        }

        return false;
    }

    public function view(User $user, Entity $resource): bool
    {
        if (!$this->canView($user, $resource)) {
            return false;
        }

        if (!$this->show($user, $resource->placement)) {
            return false;
        }

        return true;
    }

    public function create(User $user, ?User $owner = null): bool
    {
        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if (!$user->hasPermissionTo('advertise.create')) {
            return false;
        }

        return true;
    }

    public function update(User $user, ?Entity $resource = null): bool
    {
        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if (!$user->hasPermissionTo('advertise.update')) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        if ($resource->status === Support::ADVERTISE_STATUS_COMPLETED) {
            return false;
        }

        return true;
    }

    public function delete(User $user, ?Entity $resource = null): bool
    {
        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if (null === $resource) {
            return false;
        }

        return $this->deleteOwn($user, $resource);
    }

    public function deleteOwn(User $user, ?Entity $resource = null): bool
    {
        if (!$user->hasPermissionTo('advertise.delete')) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function viewOnProfilePage(User $user, User $owner): bool
    {
        return false;
    }

    public function payment(User $user, Entity $resource): bool
    {
        /**
         * @var Advertise $resource
         */
        if (null === $resource->placement) {
            return false;
        }

        if ($resource->status != Support::ADVERTISE_STATUS_UNPAID) {
            return false;
        }

        $placementPrices = $resource->placement->price;

        if (!is_array($placementPrices)) {
            return false;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        $hasLatestInvoice = null !== $resource->latestUnpaidInvoice;

        $currencyId = match ($hasLatestInvoice) {
            true    => $resource->latestUnpaidInvoice->currency_id,
            default => app('currency')->getUserCurrencyId($user),
        };

        if (!Arr::has($placementPrices, $currencyId)) {
            return false;
        }

        if (!is_numeric(Arr::get($placementPrices, $currencyId))) {
            return false;
        }

        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function show(User $user, Placement $placement): bool
    {
        if (!Settings::get('advertise.enable_advertise', true)) {
            return false;
        }

        if (!$user->hasPermissionTo('advertise.view')) {
            return false;
        }

        if (!is_array($placement->allowed_user_roles)) {
            return true;
        }

        $role = resolve(RoleRepository::class)->roleOf($user);

        if (in_array($role->entityId(), $placement->allowed_user_roles)) {
            return true;
        }

        return false;
    }

    public function updateTotal(User $user, Entity $resource): bool
    {
        if (!$this->view($user, $resource)) {
            return false;
        }

        $total = Facade::getAmount($resource);

        if (null === $total) {
            return false;
        }

        if (0 == $total) {
            return true;
        }

        if (null === $resource->statistic) {
            return false;
        }

        $current = Facade::getCurrentAmount($resource);

        if (null === $current) {
            return false;
        }

        if ($current < $total) {
            return true;
        }

        return false;
    }

    public function approve(User $user, Entity $resource): bool
    {
        if (!$resource->is_pending && !$resource->is_denied) {
            return false;
        }

        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        return false;
    }

    public function deny(User $user, Entity $resource): bool
    {
        if (!$resource->is_pending) {
            return false;
        }

        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        return false;
    }

    public function hide(User $user, Entity $resource): bool
    {
        return resolve(AdvertiseHidePolicy::class)->hide($user, $resource);
    }

    public function active(User $user, Entity $resource): bool
    {
        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function viewDetail(User $user, Entity $resource): bool
    {
        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        if (!$user->hasPermissionTo('advertise.view')) {
            return false;
        }

        if ($resource instanceof Advertise && $resource->is_pending) {
            return $this->viewPending($user, $resource);
        }

        if ($user->entityId() != $resource->userId()) {
            return false;
        }

        return true;
    }

    public function markAsPaid(User $user, Entity $resource): bool
    {
        if ($resource->status != Support::ADVERTISE_STATUS_UNPAID) {
            return false;
        }

        if (null === $resource->placement) {
            return false;
        }

        if (!$resource->placement->is_active) {
            return false;
        }

        if ($this->hasAdminCPAccess($user)) {
            return true;
        }

        return false;
    }
}
