<?php

namespace MetaFox\Event\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\Member;
use MetaFox\Event\Repositories\MemberRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class MemberPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MemberPolicy
{
    use HasPolicyTrait;
    use HandlesAuthorization;

    protected string $type = Member::ENTITY_TYPE;

    public function memberRepository(): MemberRepositoryInterface
    {
        return resolve(MemberRepositoryInterface::class);
    }

    public function viewOwner(User $user, ?User $owner = null): bool
    {
        if ($owner == null) {
            return false;
        }

        // Check can view on owner.
        if (!PrivacyPolicy::checkPermissionOwner($user, $owner)) {
            return false;
        }

        return true;
    }

    public function viewAny(User $user, ?User $owner = null): bool
    {
        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if ($owner instanceof User) {
            if (!$this->viewOwner($user, $owner)) {
                return false;
            }
        }

        return true;
    }

    public function delete(User $user, ?Content $resource = null): bool
    {
        if ($user->hasPermissionTo('event.moderate')) {
            return true;
        }

        if (!$resource instanceof Event) {
            return false;
        }

        return $resource->isModerator($user);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function joinEvent(User $user, ?Content $resource = null): bool
    {
        if (PrivacyPolicy::checkPermissionOwner($user, $resource?->owner)) {
            return true;
        }

        if (!policy_check(EventPolicy::class, 'view', $user, $resource)) {
            return false;
        }

        return true;
    }

    /**
     * @param User  $user
     * @param Event $resource
     *
     * @return bool
     */
    public function leaveEvent(User $user, ?Content $resource = null): bool
    {
        if (!policy_check(EventPolicy::class, 'view', $user, $resource)) {
            return false;
        }

        if (!policy_check(EventPolicy::class, 'updateRsvp', $user, $resource)) {
            return false;
        }

        return true;
    }

    /**
     * @param User  $user
     * @param Event $resource
     *
     * @return bool
     */
    public function interestedInEvent(User $user, ?Content $resource = null): bool
    {
        if (!policy_check(EventPolicy::class, 'view', $user, $resource)) {
            return false;
        }

        return policy_check(EventPolicy::class, 'updateRsvp', $user, $resource);
    }

    public function manageHosts(User $user, Content $resource): bool
    {
        if (!$resource instanceof Event) {
            return false;
        }

        return policy_check(EventPolicy::class, 'manageHosts', $user, $resource);
    }

    public function deleteMember(User $user, Member $resource): bool
    {
        if ($resource->hasHostPrivileges()) {
            if (!$this->removeHost($user, $resource)) {
                return false;
            }
        }

        $event = $resource->event;

        if ($this->delete($user, $event)) {
            return true;
        }

        return $resource->userId() == $user->userId();
    }

    public function removeHost(User $user, ?Member $resource): bool
    {
        if (!$resource) {
            return false;
        }

        if (!$resource->hasHostPrivileges()) {
            return false;
        }

        $event = $resource->event;

        $member = $resource->user;

        if (!$event instanceof Event) {
            return false;
        }

        if ($event->isAdmin($member)) {
            // rule: can not remove the event creator
            return false;
        }

        if ($this->manageHosts($user, $event)) {
            return true;
        }

        return $user->entityId() == $resource->userId();
    }
}
