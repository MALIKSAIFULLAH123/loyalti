<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBirthday;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Traits\UserLocationTrait;
use MetaFox\User\Traits\UserStatisticTrait;

/**
 * Class UserPreview.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserPreview extends JsonResource
{
    use UserStatisticTrait;
    use UserLocationTrait;
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

        return [
            'id'                   => $this->resource->entityId(),
            'module_name'          => $this->resource->entityType(),
            'resource_name'        => $this->resource->entityType(),
            'full_name'            => $this->resource->full_name,
            'display_name'         => $this->resource->display_name,
            'avatar'               => $profile->avatar,
            'cover'                => $profile->cover,
            'cover_photo_position' => $profile->cover_photo_position,
            'statistic'            => $this->getStatistic(),
            'friendship'           => UserFacade::getFriendship($context, $this->resource),
            'link'                 => $this->resource?->toLink(),
            'privacy'              => MetaFoxPrivacy::EVERYONE,
            'description'          => $summary,
            'age'                  => UserBirthday::getCurrentAgeByUser($this->resource),
            'new_age_phrase'       => UserBirthday::getFormattedUpcomingAgeByUser($this->resource),
            'birthday'             => UserBirthday::getTranslatedBirthday($this->resource),
            'location'             => $this->getLocation($context, $this->resource),
            'is_featured'          => (bool) $this->resource->is_featured,
            'is_deleted'           => $this->resource->isDeleted(),
            'profile_settings'     => UserPrivacy::hasAccessProfileSettings($context, $this->resource),
            'extra'                => $this->getExtra(),
        ];
    }
}
