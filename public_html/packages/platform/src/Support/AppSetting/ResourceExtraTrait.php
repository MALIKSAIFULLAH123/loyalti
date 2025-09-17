<?php

namespace MetaFox\Platform\Support\AppSetting;

use Illuminate\Database\Eloquent\Relations\Relation;
use MetaFox\Core\Traits\CheckModeratorSettingTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission as ACL;

/**
 * Trait ResourceExtraTrait.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity
 */
trait ResourceExtraTrait
{
    use CheckModeratorSettingTrait;

    /**
     * @param Content $resource
     * @param User    $user
     *
     * @return array<string, bool>
     */
    private function getResourceExtra(Content $resource, User $user): array
    {
        $viewComment = $this->viewComment($user, $resource);

        return [
            ACL::CAN_SEARCH          => true,
            ACL::CAN_VIEW_COMMENT    => $viewComment,
            ACL::CAN_VIEW            => $user->can('view', [$resource, $resource]),
            ACL::CAN_LIKE            => $user->can('like', [$resource, $resource]),
            ACL::CAN_VIEW_REACTION   => $this->canViewReaction($user, $resource),
            ACL::CAN_SHARE           => $user->can('share', [$resource, $resource]),
            ACL::CAN_DELETE          => $user->can('delete', [$resource, $resource]),
            ACL::CAN_DELETE_OWN      => $user->can('deleteOwn', [$resource, $resource]),
            ACL::CAN_REPORT          => $user->can('reportItem', [$resource, $resource]),
            ACL::CAN_REPORT_TO_OWNER => $user->can('reportToOwner', [$resource, $resource]),
            ACL::CAN_ADD             => $user->can('create', []),
            ACL::CAN_EDIT            => $user->can('update', [$resource, $resource]),
            ACL::CAN_COMMENT         => $user->can('comment', [$resource, $resource]) && $viewComment,
            ACL::CAN_PUBLISH         => $user->can('publish', [$resource, $resource]),

            /* @deprecated Remove on 5.2.0. Do not remove now because it affects to old apps which are not upgraded to 5.1.10 */
            ACL::CAN_FEATURE         => $resource instanceof HasFeature && $user->can('feature', [$resource, $resource, !$resource->is_featured]),

            ACL::CAN_APPROVE                  => $this->canApprove($user, $resource),
            ACL::CAN_SAVE_ITEM                => $user->can('saveItem', [$resource, $resource]),
            ACL::CAN_SPONSOR                  => $user->can('sponsor', [$resource]),
            ACL::CAN_PURCHASE_SPONSOR         => $user->can('purchaseSponsor', [$resource]),
            ACL::CAN_UNSPONSOR                => $user->can('unsponsor', [$resource]),
            ACL::CAN_SPONSOR_IN_FEED          => $user->can('sponsorInFeed', [$resource]),
            ACL::CAN_PURCHASE_SPONSOR_IN_FEED => $user->can('purchaseSponsorInFeed', [$resource]),
            ACL::CAN_UNSPONSOR_IN_FEED        => $user->can('unsponsorInFeed', [$resource]),
            ACL::CAN_SHOW_SPONSOR_LABEL       => $user->can('showSponsorLabel', [$resource]),
            ACL::CAN_FEATURE_FREE             => $user->can('featureFree', [$resource]),
            ACL::CAN_PURCHASE_FEATURE         => $user->can('purchaseFeature', [$resource]),
            ACL::CAN_UNFEATURE                => $user->can('unfeature', [$resource]),
        ];
    }

    protected function canApprove(User $user, Content $resource): bool
    {
        if ($resource instanceof User) {
            return $user->can('approve', [$resource, $resource]);
        }

        $owner = $resource->owner;

        if ($owner instanceof HasPrivacyMember) {
            if ($resource->isApproved()) {
                return false;
            }

            return $this->checkModeratorSetting($user, $owner, 'approve_or_deny_post');
        }

        return $user->can('approve', [$resource, $resource]);
    }

    protected function viewComment(User $user, Content $resource): bool
    {
        if (!app_active('metafox/comment')) {
            return false;
        }

        $owner = $resource?->owner;

        if ($owner instanceof HasPrivacyMember) {
            return $this->invokePolicyMethod($user, $owner);
        }

        return $this->invokePolicyMethod($user, $resource);
    }

    protected function canViewReaction(User $user, Content $resource): bool
    {
        if (!app_active('metafox/like')) {
            return false;
        }

        $modelName = Relation::getMorphedModel('like');
        if ($modelName === null) {
            return false;
        }

        $policy = PolicyGate::getPolicyFor($modelName);
        if (null == $policy) {
            return false;
        }

        if (method_exists($policy, 'viewAny')) {
            return $policy->viewAny($user);
        }

        return false;
    }

    private function invokePolicyMethod(User $user, Content $resource): bool
    {
        $policy = PolicyGate::getPolicyFor(get_class($resource));

        if (null == $policy) {
            return !($resource instanceof HasPrivacyMember);
        }

        if (method_exists($policy, 'viewComment')) {
            return $policy->viewComment($user, $resource);
        }

        return true;
    }
}
