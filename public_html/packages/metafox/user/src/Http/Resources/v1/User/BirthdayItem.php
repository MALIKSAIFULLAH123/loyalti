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
use MetaFox\User\Traits\UserLocationTrait;
use MetaFox\User\Traits\UserStatisticTrait;

/**
 * Class BirthdayItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class BirthdayItem extends JsonResource
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
            'resource_name'        => 'user',
            'full_name'            => $this->resource->full_name,
            'avatar'               => $profile->avatar,
            'month'                => $profile->birthday_month,
            'cover'                => $profile->cover,
            'cover_photo_position' => $profile->cover_photo_position,
            'friendship'           => UserFacade::getFriendship($context, $this->resource),
            'link'                 => $this->resource?->toLink(),
            'privacy'              => MetaFoxPrivacy::EVERYONE,
            'description'          => $summary,
            'age'                  => UserBirthday::getCurrentAgeByUser($this->resource),
            'new_age_phrase'       => UserBirthday::getFormattedUpcomingAgeByUser($this->resource),
            'birthday'             => UserBirthday::getTranslatedBirthday($this->resource),
            'birthday_format'      => UserBirthday::formatBirthdayForClient($this->resource),
            'is_deleted'           => $this->resource->isDeleted(),
        ];
    }
}
