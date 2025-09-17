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
use MetaFox\User\Repositories\CustomFieldRepositoryInterface;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBirthday;
use MetaFox\User\Support\Facades\UserBlocked;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Traits\FriendStatisticTrait;
use MetaFox\User\Traits\UserLocationTrait;
use MetaFox\User\Traits\UserStatisticTrait;

/**
 * Class UserItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserItem extends JsonResource
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
        $context      = user();
        $profile      = $this->resource?->profile;
        $userActivity = $this->resource?->userActivity;

        $summary  = UserFacade::getSummary($context, $this->resource);
        $location = $this->resource ? $this->getLocation($context, $this->resource) : [];

        $data = [
            'id'                   => $this->resource?->entityId(),
            'module_name'          => $this->resource?->entityType(),
            'resource_name'        => $this->resource?->entityType(),
            'full_name'            => $this->resource?->full_name,
            'display_name'         => $this->resource?->display_name,
            'user_name'            => $this->resource?->user_name,
            'avatar'               => $profile?->avatars,
            'avatar_id'            => $this->getAvatarId($context, $this->resource),
            'last_name'            => $this->resource?->last_name ?? UserFacade::getLastName($this->resource?->full_name),
            'first_name'           => $this->resource?->first_name ?? UserFacade::getFirstName($this->resource?->full_name),
            'gender'               => $profile?->genderObject(),
            'language_id'          => $profile?->language_id,
            'joined'               => $this->resource?->created_at, // formatted to ISO-8601 as v4
            'last_activity'        => $userActivity?->last_activity,
            'time_zone'            => Timezone::getName($profile?->timezone_id),
            'cover'                => $profile?->covers,
            'cover_photo_id'       => $profile?->getCoverId(),
            'cover_photo_position' => $profile?->cover_photo_position,
            'profile_settings'     => $this->resource ? UserPrivacy::hasAccessProfileSettings($context, $this->resource) : null,
            'post_types'           => [],
            'summary'              => $summary,
            'activity_total'       => 0,
            'activity_points'      => 0,
            'is_featured'          => $this->resource?->is_featured,
            'age'                  => $this->resource ? UserBirthday::getCurrentAgeByUser($this->resource) : null,
            'is_blocked'           => $this->isBlocked(),
            'short_name'           => $this->resource ? UserFacade::getShortName($this->resource?->full_name) : null,
            'creation_date'        => $this->resource?->created_at,
            'modification_date'    => $this->resource?->updated_at,
            'link'                 => $this->resource?->toLink(),
            'url'                  => $this->resource?->toUrl(),
            'statistic'            => $this->getStatistic(),
            'friend_statistic'     => $this->getFriendStatistic(),
            'extra'                => $this->getExtra(),
            'friends'              => [],
            'privacy'              => 0,
            'is_owner'             => $profile?->isOwner($context),
            'status_id'            => 0,
            'message'              => '',
            'is_following'         => $this->resource ? UserFacade::isFollowing($context, $this->resource) : null,
            'friendship'           => $this->resource ? UserFacade::getFriendship($context, $this->resource) : null,
            'profile'              => $this->resource?->customProfile(),
            'new_age_phrase'       => $this->resource ? UserBirthday::getFormattedUpcomingAgeByUser($this->resource) : null,
            'birthday'             => $this->resource ? UserBirthday::getTranslatedBirthday($this->resource) : null,
        ];

        $data            = array_merge($data, $location);
        $extraAttributes = $this->getExtraAttributes($context);
        $data            = array_merge($data, $extraAttributes);

        return $data;
    }

    /**
     * @return bool
     * @throws AuthenticationException
     */
    protected function isBlocked()
    {
        return UserBlocked::isBlocked($this->resource, user());
    }

    /**
     * This method shall be removed from version 5.2
     * Temporary solution for mobile app to avoid crash.
     *
     * @param User      $context
     * @param User|null $user
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
