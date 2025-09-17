<?php

namespace MetaFox\User\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Repositories\UserProfileRepositoryInterface;

class UserProfileRepository extends AbstractRepository implements UserProfileRepositoryInterface
{
    public function model()
    {
        return UserProfile::class;
    }

    public function checkUpdatePermission(ContractUser $context, ContractUser $user, array $attributes): void
    {
        $dottedArrayKey      = array_keys(Arr::dot($attributes));
        $accountUpdateFields = $this->getAccountUpdateField();
        $profileUpdateFields = $this->getProfileUpdateField($user);

        if (!empty(array_intersect($dottedArrayKey, $accountUpdateFields))) {
            policy_authorize(UserPolicy::class, 'updateSetting', $context, $user);
        }

        if (!empty(array_intersect($dottedArrayKey, $profileUpdateFields))) {
            policy_authorize(UserPolicy::class, 'update', $context, $user);
        }
    }

    protected function getAccountUpdateField(): array
    {
        return [
            'user_name',
            'full_name',
            'email',
            'profile.language_id',
            'profile.currency_id',
            'phone_number',
        ];
    }

    protected function getProfileUpdateField(ContractUser $user): array
    {
        $fields = array_merge([
            'birthday', 'postal_code', 'country_iso', 'country_city_code',
            'gender', 'relation', 'relation_with', 'address', 'gender_id', 'city_location', 'country_state_id',
        ], CustomFieldFacade::loadFieldName($user, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_ALL,
        ]));

        foreach ($fields as &$field) {
            $field = 'profile.' . $field;
        }

        return $fields;
    }
}
