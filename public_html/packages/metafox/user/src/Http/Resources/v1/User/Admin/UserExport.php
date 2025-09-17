<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Core\Support\Facades\Timezone;
use MetaFox\Platform\Contracts\User;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserActivity;
use MetaFox\User\Support\Browse\Traits\User\ExtraTrait;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserBirthday;
use MetaFox\User\Traits\UserLocationTrait;
use MetaFox\User\Traits\UserStatisticTrait;

/**
 * Class UserDetail.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserExport extends JsonResource
{
    use ExtraTrait;
    use UserStatisticTrait;
    use UserLocationTrait;

    protected ?User $context = null;

    public function setContext(?User $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string,           mixed>
     */
    public function toArray($request): array
    {
        $profile    = $this->resource?->profile;
        $languageId = $profile?->language_id;
        $custom     = $this->resource?->customProfile();

        $ipAddress    = '';
        $lastActivity = '';
        $lastLogin    = '';

        $userActivity = $this->resource->userActivity;

        if ($userActivity instanceof UserActivity) {
            $ipAddress    = $userActivity->last_ip_address;
            $lastActivity = $userActivity->last_activity;
            $lastLogin    = $userActivity->last_login;
        }

        $approvalStatus = __p('user::phrase.approved');
        if ($this->resource?->isNotApproved()) {
            $approvalStatus = __p('user::phrase.not_approved');
        }

        if ($this->resource?->isPendingApproval()) {
            $approvalStatus = __p('core::phrase.pending');
        }

        $data = [
            'id'                  => $this->resource?->entityId(),
            'display_name'        => $this->resource?->display_name,
            'user_name'           => $this->resource?->user_name,
            'email'               => $this->resource?->getEmailForVerification(),
            'phone_number'        => $this->resource?->getPhoneNumberForVerification(),
            'role'                => $this->resource?->getRole()?->name,
            'gender'              => $profile ? UserFacade::getGender($profile) : null,
            'birthday'            => $this->resource ? UserBirthday::getTranslatedBirthday($this->resource) : null,
            'age'                 => $this->resource ? UserBirthday::getCurrentAgeByUser($this->resource) : null,
            'relationship_text'   => $profile?->relationship_text,
            'summary'             => $this->context ? UserFacade::getSummary($this->context, $this->resource) : null,
            'language_name'       => $languageId ? Language::getName($languageId) : '',
            'time_zone'           => Timezone::getName($profile->timezone_id),
            'location'            => $this->resource && $this->context ? UserFacade::getAddress($this->context, $this->resource) : null,
            'country'             => $profile?->country_iso ? Country::getCountryName($profile->country_iso) : null,
            'city'                => $profile?->city_location,
            'state'               => $profile?->country_iso ? Country::getCountryStateName($profile->country_iso, $profile->country_state_id) : null,
            'postal_code'         => $profile?->postal_code,
            'currency'            => $profile?->currency_id,
            'ip_address'          => $ipAddress,
            'creation_date'       => $this->resource?->created_at,
            'last_activity'       => $lastActivity,
            'last_login'          => $lastLogin,
            'approval_status'     => $approvalStatus,
            'verification_status' => $this->resource?->hasVerified() ? __p('user::phrase.verified') : __p('user::phrase.unverified'),
            'avatar_url'          => $profile?->avatar,
            'cover_url'           => $profile?->cover,
            'profile_url'         => $this->resource?->toUrl(),
        ];

        return array_merge($data, $custom);
    }
}
