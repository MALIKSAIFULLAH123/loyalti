<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Form\GenderTrait;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\RelationTrait;
use MetaFox\Form\Section as SectionForm;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Profile\Support\Facade\CustomProfile;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityDetail;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Yup\Yup;
use stdClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class UserProfileMobileForm extends AbstractForm
{
    use RelationTrait;
    use GenderTrait;

    /**
     * @throws AuthenticationException
     */
    public function boot(UserRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $id ? $repository->find($id) : user();

        policy_authorize(UserPolicy::class, 'update', user(), $this->resource);
    }

    protected function prepare(): void
    {
        /** @var UserProfile $profile */
        $profile = $this->resource->profile;

        $user = new StdClass();
        if (!empty($profile->relation_with)) {
            try {
                $user = UserEntity::getById($profile->relation_with);
                $user = new UserEntityDetail($user);
            } catch (\Exception $exception) {
            }
        }

        $data = array_merge([
            'country_iso'       => $profile->country_iso,
            'country_state'     => $this->getCountryState($profile ?? null),
            'country_city_code' => $this->getCityCode(),
            'postal_code'       => $profile->postal_code,
            'birthday'          => $profile->birthday,
            'address'           => $profile->address,
        ], CustomProfile::denormalize($this->resource, [
            'for_form'     => true,
            'section_type' => CustomField::SECTION_TYPE_USER,
        ]));

        if ($profile->gender) {
            Arr::set($data, 'gender', $profile->gender?->entityId());
        }

        Arr::forget($data, ['relation', 'relation_with']);

        if ($profile->relation) {
            Arr::set($data, 'relation', $profile->relation);
            Arr::set($data, 'relation_with', $user);
        }

        $this->title(__p('user::phrase.edit_profile_info'))
            ->action(url_utility()->makeApiUrl("user/profile/{$this->resource->entityId()}"))
            ->setBackProps(__p('core::phrase.back'))
            ->asPut()
            ->setValue($data);
    }

    /**
     * @throws AuthenticationException
     */
    public function initialize(): void
    {
        CustomFieldFacade::loadFieldsEdit($this, $this->resource, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'resolution'   => MetaFoxConstant::RESOLUTION_MOBILE,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getCityCode(): ?array
    {
        /** @var UserProfile $profile */
        $profile  = $this->resource->profile;
        $cityCode = null;
        if (!empty($profile->country_city_code)) {
            $value = is_numeric($profile->country_city_code) ? (int) $profile->country_city_code : $profile->country_city_code;

            $cityCode = [
                'label' => $profile->city_location,
                'value' => $value,
            ];
        }

        return $cityCode;
    }

    /**
     * @param UserProfile|null $profile
     * @return array<string>|null
     */
    protected function getCountryState(?UserProfile $profile): ?array
    {
        if (!$profile instanceof UserProfile) {
            return null;
        }

        $countryIso = $profile?->country_iso;
        $stateId    = $profile?->country_state_id;

        if (!is_string($countryIso) || !is_string($stateId)) {
            return null;
        }

        return [
            'value' => $stateId,
            'label' => Country::getCountryStateName($countryIso, $stateId),
        ];
    }

    public function getFieldInBasicInfoSection(SectionForm $sectionForm): void
    {
        $fieldCollections = $this->fieldRepository()->getFieldCollectionsByBasicInfoSection();

        /** @var Field[] $fieldCollections */
        foreach ($fieldCollections as $field) {
            $methodName = 'build' . Str::studly($field->field_name) . 'Field';

            $sectionField = $field->section;

            $sectionForm->label($sectionField->label);
            $this->$methodName($field, $sectionForm);
        }
    }

    protected function buildRelationshipField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $yup = $field->is_required ? Yup::number()->required() : Yup::number()->nullable();

        $section->addField(
            Builder::choice('relation')
                ->label($field->label)
                ->setAttribute('dependField', 'relation_with')
                ->required($field->is_required)
                ->setAttribute('disableUncheck', true)
                ->options($this->getRelations())
                ->yup($yup)
        );

        if (!app_active('metafox/friend')) {
            return;
        }

        $section->addFields(
            Builder::friendPicker('relation_with')
                ->placeholder(__p('friend::phrase.search_friends_by_their_name'))
                ->setAttribute('api_endpoint', 'friend')
                ->showWhen(['includes', 'relation', $this->getWithRelations()]),
        );
    }

    protected function buildGenderField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $yup = $field->is_required ? Yup::number()->required() : Yup::number()->nullable();

        $section->addField(
            Builder::choice('gender')
                ->label($field->label)
                ->required($field->is_required)
                ->enableSearch()
                ->options($this->getDefaultGenders($this->resource))
                ->yup($yup)
        );
    }

    protected function buildBirthdateField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $minYear         = Settings::get('user.date_of_birth_start', 1900);
        $maxYear         = Settings::get('user.date_of_birth_end', Carbon::now()->year);
        $minDate         = Carbon::create($minYear);
        $maxDate         = Carbon::create($maxYear);
        $minDateString   = $minDate ? $minDate->toDateString() : $minYear;
        $maxDateString   = $maxDate ? $maxDate->endOfYear()->toDateString() : $maxYear;
        $birthdayMessage = __p('validation.invalid_date_of_birth_between', [
            'date_start' => $minDateString,
            'date_end'   => $maxDateString,
        ]);
        $yup             = $field->is_required ? Yup::date()->required() : Yup::date()->nullable();

        $section->addField(
            Builder::birthday('birthday')
                ->label($field->label)
                ->required($field->is_required)
                ->setAttribute('minDate', $minDateString)
                ->setAttribute('maxDate', $maxDateString)
                ->yup(
                    $yup->minYear((string) $minYear, $birthdayMessage)
                        ->maxYear((string) $maxYear, $birthdayMessage)
                        ->setError('typeError', __p('validation.this_field_is_not_a_valid_data'))
                )
        );
    }

    protected function buildLocationField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $countries = Country::buildCountrySearchForm();
        $yup       = $field->is_required
            ? Yup::string()->required(__p('user::validation.country_is_a_required_field'))
            : Yup::string()->nullable();

        $section->addFields(
            Builder::choice('country_iso')
                ->label(__p('localize::country.country'))
                ->setAttribute('resetValue', true)
                ->options($countries)
                ->required($field->is_required)->yup($yup),
            Builder::countryStatePicker('country_state')
                ->label(__p('localize::country.state'))
                ->description(__p('localize::country.state_name'))
                ->searchEndpoint('user/country/state')
                ->showWhen(['and', ['truthy', 'country_iso']])
                ->searchParams([
                    'country' => ':country_iso',
                ]),
            Builder::countryCity('country_city_code')
                ->label(__p('localize::country.city'))
                ->description(__p('localize::country.city_name'))
                ->showWhen([
                    'truthy',
                    'country_iso',
                ])
                ->searchEndpoint('user/city')
                ->searchParams([
                    'country'   => ':country_iso',
                    'state'     => ':country_state',
                    'city_code' => ':country_city_code',
                ]),
            Builder::text('postal_code')
                ->label(__p('user::phrase.postal_code'))
                ->placeholder('- - - - - -'),
        );
    }

    protected function buildAddressField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $yup = $field->is_required ? Yup::string()->required() : Yup::string()->nullable();
        $section->addField(
            Builder::text('address')
                ->required($field->is_required)
                ->label($field->label)
                ->yup($yup)
        );
    }

    protected function fieldRepository(): FieldRepositoryInterface
    {
        return resolve(FieldRepositoryInterface::class);
    }
}
