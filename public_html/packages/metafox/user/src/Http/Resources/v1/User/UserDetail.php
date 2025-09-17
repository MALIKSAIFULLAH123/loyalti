<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Core\Support\Facades\Timezone;
use MetaFox\Platform\Contracts\HasAvatarMorph;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFox;
use MetaFox\Profile\Support\CustomField;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBirthday;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Traits\FriendStatisticTrait;
use MetaFox\User\Traits\UserLocationTrait;
use MetaFox\User\Traits\UserStatisticTrait;

/**
 * Class UserDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserDetail extends JsonResource
{
    use ExtraTrait;
    use UserStatisticTrait;
    use UserLocationTrait;
    use FriendStatisticTrait;

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
        $profile = $this->resource?->profile;

        $context = user();

        $summary    = UserFacade::getSummary($context, $this->resource);
        $languageId = $profile->language_id;

        $custom = $this->resource?->customProfile();

        $data = [
            'id'                     => $this->resource?->entityId(),
            'module_name'            => $this->resource?->entityType(),
            'resource_name'          => $this->resource?->entityType(),
            'full_name'              => $this->resource?->full_name,
            'display_name'           => $this->resource->display_name,
            'user_name'              => $this->resource?->user_name,
            'avatar'                 => $profile->avatars,
            'avatar_id'              => $this->getAvatarId($context, $this->resource),
            'last_name'              => $this->resource?->last_name ?? UserFacade::getLastName($this->resource?->full_name),
            'first_name'             => $this->resource?->first_name ?? UserFacade::getFirstName($this->resource?->full_name),
            'gender'                 => $profile->genderObject(),
            'language_id'            => $languageId,
            'language_name'          => $languageId ? Language::getName($languageId) : '',
            'joined'                 => $this->resource?->created_at, // formatted to ISO-8601 as v4
            'time_zone'              => Timezone::getName($profile->timezone_id),
            'currency_id'            => $profile->currency_id,
            'defaultActiveTabMenu'   => $this->resource?->getDefaultTabMenu(),
            'cover'                  => $profile->covers,
            'cover_photo_id'         => $profile->getCoverId(),
            'cover_photo_position'   => $profile->cover_photo_position,
            'profile_settings'       => $this->resource ? UserPrivacy::hasAccessProfileSettings($context, $this->resource) : null,
            'profile_menu_settings'  => $this->resource ? UserPrivacy::hasAccessProfileMenuSettings($context, $this->resource) : null,
            'post_types'             => [],
            'summary'                => $summary,
            'activity_total'         => 0,
            'activity_points'        => $this->getAvailablePoints(),
            'is_featured'            => $this->resource?->is_featured,
            'is_pending'             => $this->resource->isPendingApproval(),
            'is_denied'              => $this->resource->isNotApproved(),
            'age'                    => $this->resource ? UserBirthday::getCurrentAgeByUser($this->resource) : null,
            'is_blocked'             => $this->isBlocked(),
            'short_name'             => UserFacade::getShortName($this->resource?->full_name),
            'creation_date'          => $this->resource?->created_at,
            'modification_date'      => $this->resource?->updated_at,
            'link'                   => $this->resource?->toLink(),
            'url'                    => $this->resource?->toUrl(),
            'statistic'              => array_merge($this->getStatistic(), $this->getFriendStatistic()),
            'extra'                  => $this->getExtra(),
            'friends'                => [],
            'privacy'                => 0,
            'is_owner'               => $profile->isOwner($context),
            'status_id'              => 0,
            'message'                => '',
            'friendship'             => $this->resource ? UserFacade::getFriendship($context, $this->resource) : null,
            'is_following'           => $this->resource ? UserFacade::isFollowing($context, $this->resource) : null,
            'total_follower'         => $this->resource ? UserFacade::totalFollowers($this->resource) : null,
            'bio'                    => $this->getCustomProfile($custom, 'bio'),
            'interest'               => $this->getCustomProfile($custom, 'interest'),
            'about_me'               => $this->getCustomProfile($custom, 'about_me'),
            'hobbies'                => $this->getCustomProfile($custom, 'hobbies'),
            'address'                => $this->resource ? UserFacade::getAddress($context, $this->resource) : null,
            'birthday'               => $this->resource ? UserBirthday::getTranslatedBirthday($this->resource) : null,
            'gender_text'            => $this->resource ? UserFacade::getGender($profile) : null,
            'relationship_text'      => $profile->relationship_text,
            'is_deleted'             => $this->resource->isDeleted(),
            'cover_resource'         => $this->getCoverResources(),
            'avatar_resource'        => $this->getAvatarResources(),
            'privacy_feed'           => $this->getPrivacyFeed(),
            'avatar_thumbnail_sizes' => $profile instanceof HasAvatarMorph ? $profile->getAvatarSizes() : ['50x50', '120x120', '200x200'],
            'cover_thumbnail_sizes'  => $profile instanceof HasCoverMorph ? $profile->getCoverSizes() : ['240', '500', '1024'],
        ];

        $data = array_merge($data, $this->getLocation($context, $this->resource));

        $extraAttributes = $this->getExtraAttributes($context);
        $data            = array_merge($data, $extraAttributes);

        return $data;
    }

    /**
     * @return bool
     * @throws AuthenticationException
     */
    protected function isBlocked(): bool
    {
        return $this->resource && UserBlocked::isBlocked($this->resource, user());
    }

    protected function getCoverResources(): ?JsonResource
    {
        if ($this->resource === null) {
            return null;
        }

        $profile = $this->resource->profile;
        if (!$profile->cover_id || !$profile->cover_type) {
            return null;
        }

        return !empty($profile->cover)
            ? ResourceGate::asDetail($profile->cover()->first())
            : null;
    }

    protected function getAvatarResources(): ?JsonResource
    {
        if ($this->resource === null) {
            return null;
        }

        $profile = $this->resource->profile;
        if (!$profile->avatar_type || !$profile->avatar_id) {
            return null;
        }

        return !empty($profile->avatar)
            ? ResourceGate::asDetail($profile->avatar()->first())
            : null;
    }

    protected function getCustomProfile(array $customs, string $key, bool $parseUrl = true): ?string
    {
        $key   = sprintf(CustomField::FIELD_USER_TYPE_NAME, 'user', $key);
        $value = Arr::get($customs, $key);

        if (!$parseUrl) {
            return $value;
        }

        if ($value) {
            $value = parse_output()->parseUrl($value);
        }

        return $value;
    }

    protected function getPrivacyFeed(): ?array
    {
        if ($this->resource === null) {
            return null;
        }

        $privacy = UserPrivacy::getProfileSetting($this->resource->entityId(), 'feed:view_wall');

        $privacyDetail = app('events')->dispatch(
            'activity.get_privacy_detail',
            [user(), $this->resource, $privacy, false],
            true
        );

        if (!is_array($privacyDetail)) {
            return null;
        }

        $privacyDetail['label'] = $privacyDetail['tooltip'];

        $privacyDetail['tooltip'] = __p('core::phrase.tooltip_privacy_display_name_control_who_can_see_this', [
            'display_name' => $this->resource->full_name,
        ]);

        return $privacyDetail;
    }

    /**
     * This method shall be removed from version 5.2
     * Temporary solution for mobile app to avoid crash.
     *
     * @param User      $context
     * @param User|null $user
     *
     * @return int|null
     * @deprecated 5.2
     */
    protected function getAvatarId(User $context, ?User $user): ?int
    {
        $isMobile         = MetaFox::isMobile();
        $isViewOwnProfile = $context->entityId() && $context->entityId() === $user?->entityId();
        $profile          = $user?->profile;

        if (!$profile instanceof UserProfile) {
            return $isMobile && !$isViewOwnProfile ? -1 : 0;
        }

        $avatarId = $profile?->getAvatarId();

        return $avatarId ?: ($isMobile && !$isViewOwnProfile ? -1 : 0);
    }

    private function getAvailablePoints(): int
    {
        $pointStatistic = UserFacade::getPointStatistic(user());

        if (!$pointStatistic instanceof EloquentModel) {
            return 0;
        }

        return (int) $pointStatistic->available_points;
    }
}
