<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Http\Resources\v1\Category\CategoryEmbed;
use MetaFox\Group\Http\Resources\v1\Invite\InviteItem;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Support\Browse\Traits\Group\StatisticTrait;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Group\Support\GroupRole;
use MetaFox\Group\Support\InviteType;
use MetaFox\Group\Support\Membership;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Group\Traits\HasDefaultTabTrait;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\User as ContractsUser;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\User\Support\Facades\User;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class GroupDetail.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupDetail extends JsonResource
{
    use HasExtra;
    use StatisticTrait;
    use HasDefaultTabTrait;
    use HasHashtagTextTrait;

    private ?string $inviteCode = null;

    public function setInviteCode(string $inviteCode): self
    {
        $this->inviteCode = $inviteCode;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $covers     = $this->resource->covers;
        $inviteType = $privacyLabel = null;

        if ($this->inviteCode !== null) {
            $inviteType = InviteType::INVITED_GENERATE_LINK;
        }

        $isMuted          = Membership::isMuted($this->resource->entityId(), $this->getContext()->entityId());
        $membership       = Membership::getMembership($this->resource, $this->getContext(), $inviteType);
        $isModerator      = $this->resource->isModerator($this->getContext());
        $isApproved       = $this->resource->is_approved;
        $groupText        = $this->resource->groupText;
        $isPending        = $isViewId = !$isApproved;
        $shortDescription = $text = MetaFoxConstant::EMPTY_STRING;
        $isMember         = $isLike = $this->resource->isMember($this->getContext());

        if ($membership != Membership::JOINED) {
            $isLike = false;
        }

        if ($groupText) {
            $text             = $this->getTransformContent($groupText->text_parsed);
            $text             = parse_output()->parseItemDescription($text);
            $shortDescription = $this->getTransformContent($groupText->text_parsed);
            $shortDescription = parse_output()->parseItemDescription($shortDescription);
        }

        $privacyDetail = app('events')->dispatch(
            'activity.get_privacy_detail_on_owner',
            [$this->getContext(), $this->resource],
            true
        );

        $extra = $this->getGroupExtra();
        if ($extra['can_view_privacy']) {
            $privacyLabel = __p(PrivacyTypeHandler::PRIVACY_PHRASE[$this->resource->privacy_type]);
        }

        return [
            'id'                            => $this->resource->entityId(),
            'module_name'                   => $this->resource->entityType(),
            'resource_name'                 => $this->resource->entityType(),
            'title'                         => ban_word()->clean($this->resource->name),
            'privacy'                       => $this->resource->privacy,
            'reg_method'                    => $this->resource->privacy_type,
            'reg_name'                      => $privacyLabel,
            'category'                      => new CategoryEmbed($this->resource->category),
            'user'                          => ResourceGate::user($this->resource->userEntity),
            'text'                          => $text,
            'description'                   => $shortDescription,
            'view_id'                       => $isViewId,
            'is_liked'                      => $isLike,
            'is_member'                     => $isMember,
            'is_admin'                      => $this->resource->isAdmin($this->getContext()),
            'is_moderator'                  => $isModerator,
            'is_owner'                      => $this->resource->isUser($this->getContext()),
            'is_reg'                        => null,
            'invite'                        => $this->getInvite($inviteType),
            'is_pending'                    => $isPending,
            'is_featured'                   => $this->resource->is_featured,
            'is_sponsor'                    => $this->resource->is_sponsor,
            'is_following'                  => GroupFacade::isFollowing($this->getContext(), $this->resource),
            'pending_mode'                  => $this->resource->pending_mode,
            'membership'                    => $membership,
            'image'                         => $covers,
            'cover'                         => $covers,
            'cover_photo_position'          => empty($covers) ? null : $this->resource->cover_photo_position,
            'cover_photo_id'                => empty($covers) ? null : $this->resource->getCoverId(),
            'latitude'                      => $this->resource->location_latitude,
            'longitude'                     => $this->resource->location_longitude,
            'location_name'                 => $this->resource->location_name,
            'location_address'              => $this->resource->location_address,
            'item_type'                     => $this->resource->entityType(),
            'defaultActiveTabMenu'          => $this->getDefaultTabMenu($this->getContext(), $this->resource),
            'defaultActiveContentTab'       => $this->getDefaultContentTab($this->getContext(), $this->resource),
            'defaultActiveTabManage'        => $this->getDefaultTabManage($this->getContext(), $this->resource),
            'type_name'                     => '',
            'short_name'                    => User::getShortName(ban_word()->clean($this->resource->name)),
            'link'                          => $this->resource->toLink(),
            'url'                           => $this->resource->toUrl(),
            'pending_post_count'            => $this->getTotalPendingPost(),
            'creation_date'                 => $this->resource->created_at,
            'modification_date'             => $this->resource->updated_at,
            'profile_name'                  => $this->resource->profile_name,
            'statistic'                     => $this->getStatistic(),
            'extra'                         => $extra,
            'roles'                         => GroupRole::getGroupRolesByUser(user(), $this->resource),
            'has_membership_question'       => Membership::hasMembershipQuestion($this->resource),
            'is_rule_confirmation'          => $this->resource->is_rule_confirmation,
            'is_answer_membership_question' => $this->resource->is_answer_membership_question,
            'manages'                       => $this->getManages(),
            'is_muted'                      => $isMuted,
            'pending_invite'                => $this->getPendingInvite(),
            'profile_settings'              => UserPrivacy::hasAccessProfileSettings($this->getContext(), $this->resource),
            'profile_menu_settings'         => GroupFacade::getProfileMenuSettings($this->resource),
            'privacy_detail'                => $privacyDetail,
            'changedPrivacy'                => $this->getChangePrivacy(),
            'cover_resource'                => $this->getCoverResources(),
            'group_rule'                    => $this->getGroupRule(),
        ];
    }

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    public function getGroupExtra(): array
    {
        $extra = $this->getExtra();

        $customExtra = GroupRole::getGroupSettingPermission($this->getContext(), $this->resource->refresh());

        return array_merge($extra, $customExtra);
    }

    protected function getChangePrivacy(): ?array
    {
        $pendingPrivacy = $this->resource->getPendingPrivacy;

        if (null == $pendingPrivacy) {
            return null;
        }

        $currentPrivacyName = __p(PrivacyTypeHandler::PRIVACY_PHRASE[$this->resource->privacy_type]);
        $pendingPrivacyName = __p(PrivacyTypeHandler::PRIVACY_PHRASE[$pendingPrivacy->privacy_type]);

        return [
            'current_type' => $currentPrivacyName,
            'pending_type' => $pendingPrivacyName,
        ];
    }

    protected function getCoverResources(): ?JsonResource
    {
        if (!$this->resource->cover_id || !$this->resource->cover_type) {
            return null;
        }

        return !empty($this->resource->cover)
            ? ResourceGate::asDetail($this->resource->cover()->first())
            : null;
    }

    protected function getGroupRule(): ?array
    {
        /** @var Rule $groupRule */
        $groupRule = $this->resource->groupRules?->first();

        if (!$groupRule) {
            return null;
        }

        return [
            'title'       => $groupRule->title,
            'description' => $groupRule->description,
        ];
    }

    /**
     * @throws AuthenticationException
     */
    protected function getTotalPendingPost(): int
    {
        /** @var mixed $countPendingPost */
        $countPendingPost = app('events')->dispatch(
            'activity.count_feed_pending_on_owner',
            [$this->getContext(), $this->resource],
            true
        );

        if (!is_numeric($countPendingPost)) {
            $countPendingPost = 0;
        }

        return $countPendingPost;
    }

    /**
     * @throws AuthenticationException
     */
    protected function getContext(): ContractsUser
    {
        return user();
    }

    /**
     * @throws AuthenticationException
     */
    protected function getInvite(?string $inviteType): ?InviteItem
    {
        if (!$this->resource->isApproved()) {
            return null;
        }

        return new InviteItem(Membership::getPendingInvite($this->resource, $this->getContext(), $inviteType));
    }

    /**
     * @throws AuthenticationException
     */
    protected function getPendingInvite(): ?JsonResource
    {
        $pendingInvite = Membership::getAvailableInvite($this->resource, $this->getContext(), $this->inviteCode);

        return $pendingInvite ? ResourceGate::asItem($pendingInvite, null) : null;
    }

    protected function getManages(): array
    {
        return [
            'max_membership_questions'  => GroupFacade::getMaximumMembershipQuestion(),
            'maximum_number_group_rule' => GroupFacade::getMaximumNumberGroupRule(),
        ];
    }
}
