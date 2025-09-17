<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Profile\Support\CustomField;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Traits\UserLocationTrait;

/**
 * Class UserPropertiesSchema.
 * @property ?Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserPropertiesSchema extends JsonResource
{
    use UserLocationTrait;

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if (!$this->resource instanceof Model) {
            return $this->resourcesDefault();
        }

        $profile = $this->resource?->profile;
        $custom  = $this->resource?->customProfile();

        return [
            'id'                 => $this->resource?->entityId(),
            'title'              => $this->resource?->toTitle(),
            'full_name'          => $this->resource?->full_name,
            'first_name'         => $this->resource?->first_name,
            'last_name'          => $this->resource?->last_name,
            'user_name'          => $this->resource?->user_name,
            'email'              => $this->resource?->email,
            'phone_number'       => $this->resource?->phone_number,
            'total_follower'     => $this->resource?->total_follower,
            'short_name'         => UserFacade::getShortName($this->resource?->full_name),
            'link'               => $this->resource?->toLink(),
            'url'                => $this->resource?->toUrl(),
            'router'             => $this->resource?->toRouter(),
            'avatar'             => $profile?->avatar,
            'cover'              => $profile?->cover,
            'address'            => $profile?->address,
            'birthday'           => $profile?->birthday,
            'gender'             => $profile?->gender?->name,
            'role'               => $this->resource?->getRole()?->name,
            'roles'              => $this->resource?->roles->pluck('name')->toArray(),
            'bio'                => $this->getCustomProfile($custom, 'bio'),
            'interest'           => $this->getCustomProfile($custom, 'interest'),
            'about_me'           => $this->getCustomProfile($custom, 'about_me'),
            'hobbies'            => $this->getCustomProfile($custom, 'hobbies'),
            'country_iso'        => $profile?->country_iso,
            'city_location'      => $profile?->city_location,
            'postal_code'        => $profile?->postal_code,
            'country_city_code'  => $profile?->country_city_code,
            'country_state_id'   => $profile?->country_state_id,
            'creation_date'      => Carbon::parse($this->resource->created_at)->format('c'),
            'modification_date'  => Carbon::parse($this->resource->updated_at)->format('c'),
            'country_name'       => $profile?->country_iso ? Country::getCountryName($profile->country_iso) : null,
            'country_state_name' => $profile?->country_iso ? Country::getCountryStateName($profile->country_iso, $profile->country_state_id) : null,
        ];
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

    protected function resourcesDefault(): array
    {
        return [
            'id'                 => null,
            'title'              => null,
            'full_name'          => null,
            'first_name'         => null,
            'last_name'          => null,
            'user_name'          => null,
            'email'              => null,
            'phone_number'       => null,
            'short_name'         => null,
            'link'               => null,
            'url'                => null,
            'router'             => null,
            'avatar'             => null,
            'cover'              => null,
            'address'            => null,
            'birthday'           => null,
            'gender'             => null,
            'role'               => null,
            'roles'              => null,
            'bio'                => null,
            'interest'           => null,
            'about_me'           => null,
            'hobbies'            => null,
            'country_iso'        => null,
            'city_location'      => null,
            'postal_code'        => null,
            'country_city_code'  => null,
            'country_state_id'   => null,
            'country_name'       => null,
            'country_state_name' => null,
        ];
    }
}
