<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Support\Facades\Timezone;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBirthday;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Support\User as UserSupport;
use MetaFox\User\Traits\UserStatisticTrait;

/**
 * Class UserDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserMe extends JsonResource
{
    use UserStatisticTrait;
    use ExtraTrait;

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
        $profile = $this->resource->profile;

        $context = user();

        $summary = UserFacade::getSummary($context, $this->resource);

        $data = [
            'id'                    => $this->resource->entityId(),
            'module_name'           => $this->resource->entityType(),
            'resource_name'         => $this->resource->entityType(),
            'full_name'             => $this->resource->full_name,
            'user_name'             => $this->resource->user_name,
            'email'                 => $this->resource->email,
            'phone_number'          => $this->resource->phone_number,
            'avatar'                => $profile?->avatars,
            'avatar_id'             => $this->getAvatarId($context, $this->resource),
            'last_name'             => $this->resource->last_name ?? UserFacade::getLastName($this->resource->full_name),
            'first_name'            => $this->resource->first_name ?? UserFacade::getFirstName($this->resource->full_name),
            'gender'                => $profile?->genderObject(),
            'country_iso'           => $profile?->country_iso,
            'language_id'           => $profile?->language_id,
            'joined'                => $this->resource->created_at, // formatted to ISO-8601 as v4
            'city_location'         => $profile?->city_location,
            'time_zone'             => Timezone::getName($profile?->timezone_id),
            'cover'                 => $profile?->covers,
            'cover_photo_id'        => $profile?->getCoverId(),
            'cover_photo_position'  => $profile?->cover_photo_position,
            'profile_settings'      => UserPrivacy::hasAccessProfileSettings($context, $this->resource),
            'profile_menu_settings' => UserPrivacy::hasAccessProfileMenuSettings($context, $this->resource),
            'video_settings'        => UserFacade::getVideoSettings($this->resource),
            'post_types'            => [],
            'summary'               => $summary,
            'activity_total'        => 0,
            'activity_points'       => 0,
            'is_featured'           => $this->resource->is_featured,
            'age'                   => UserBirthday::getCurrentAgeByUser($this->resource),
            'is_blocked'            => $this->isBlocked(),
            'short_name'            => UserFacade::getShortName($this->resource->full_name),
            'creation_date'         => $this->resource->created_at,
            'modification_date'     => $this->resource->updated_at,
            'link'                  => $this->resource->toLink(),
            'statistic'             => $this->getStatistic(),
            'extra'                 => $this->getExtra('me'),
            'friends'               => [],
            'privacy'               => 0,
            'is_owner'              => $profile?->isOwner($context),
            'status_id'             => 0,
            'message'               => '',
            'friendship'            => UserFacade::getFriendship($context, $this->resource),
            'role'                  => [
                'name' => $this->resource->getRole()?->name,
                'id'   => $this->resource->getRole()?->id,
            ],
            'theme_preference'      => UserFacade::getReferenceValueByNames($this->resource, UserSupport::THEME_PREFERENCE_NAMES),
            'sort_feed_preferences' => UserFacade::getReferenceValueByName($this->resource, UserSupport::SORT_FEED_VALUES_SETTING) ?? [],
        ];

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
        return UserBlocked::isBlocked($this->resource, user());
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
}
