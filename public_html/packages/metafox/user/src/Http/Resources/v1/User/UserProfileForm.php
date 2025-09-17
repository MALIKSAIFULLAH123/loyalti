<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\GenderTrait;
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
use MetaFox\User\Repositories\UserRelationRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Yup\Yup;
use stdClass;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @todo: refactor using Form/Builder
 */
class UserProfileForm extends AbstractForm
{
    use RelationTrait;
    use GenderTrait;

    protected function prepare(): void
    {
        /** @var UserProfile $profile */
        $profile = $this->resource->profile;
        $gender  = $profile->gender;

        $user = new StdClass();
        if (!empty($profile->relation_with)) {
            try {
                $user = UserEntity::getById($profile->relation_with);
                $user = new UserEntityDetail($user);
            } catch (\Exception $exception) {

            }
        }

        $cityCode = $this->getCityCode();

        $values = array_merge([
            'country_iso'       => $profile->country_iso,
            'country_state_id'  => $profile->country_state_id,
            'country_city_code' => is_array($cityCode) ? Arr::get($cityCode, 'value') : null,
            'postal_code'       => $profile->postal_code,
            'birthday'          => $profile->birthday,
            'address'           => $profile->address,
        ], CustomProfile::denormalize($this->resource, [
            'for_form'     => true,
            'section_type' => CustomField::SECTION_TYPE_USER,
        ]));

        Arr::forget($values, ['relation', 'relation_with', 'gender', 'custom_gender']);

        if ($profile->gender) {
            Arr::set($values, 'gender', $profile->gender?->entityId());
            Arr::set($values, 'custom_gender', $gender?->is_custom ? $profile->gender?->entityId() : null);
        }

        if ($profile->relation) {
            Arr::set($values, 'relation', $profile->relation);
            Arr::set($values, 'relation_with', $user);
        }

        $this->title(__p('user::phrase.edit_profile'))
            ->action(url_utility()->makeApiUrl("user/profile/{$this->resource->entityId()}"))
            ->setBackProps(__p('core::phrase.back'))
            ->resetFormOnSuccess(false)
            ->resetDirtyWhenSuccess()
            ->asPut()
            ->setValue($values);
    }

    /**
     * @throws AuthenticationException
     */
    public function initialize(): void
    {
        CustomFieldFacade::loadFieldsEdit($this, $this->resource, [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'resolution'   => MetaFoxConstant::RESOLUTION_WEB,
        ]);

        $this->addFooter()
            ->addFields(
                Builder::submit('submit')
                    ->label(__p('core::web.update'))
                    ->sizeLarge(),
                Builder::cancelButton('_cancel')->sizeLarge(),
            );
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

    public function getRelations(): array
    {
        $repository      = resolve(UserRelationRepositoryInterface::class);
        $phpfoxRelations = $repository->getRelations();
        $data            = [];

        foreach ($phpfoxRelations as $relation) {
            /* @var UserRelation $relation */
            $data[] = [
                'value' => $relation->entityId(),
                'label' => __p($relation->phrase_var),
            ];
        }

        return $data;
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
                ->setAttribute('disableUncheck', true)
                ->required($field->is_required)
                ->options($this->getRelations())
                ->yup($yup)
        );

        if (!app_active('metafox/friend')) {
            return;
        }

        $section->addFields(
            Builder::friendPicker('relation_with')
                ->placeholder(__p('friend::phrase.search_friends_by_their_name'))
                ->setAttribute('api_endpoint', url_utility()->makeApiUrl('friend'))
                ->showWhen(['includes', 'relation', $this->getWithRelations()])
                ->resetWhenUnmount()
        );
    }

    protected function buildGenderField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $yup = $field->is_required ? Yup::number()->required() : Yup::number()->nullable();

        $section->addField(Builder::gender('gender')
            ->label($field->label)
            ->required($field->is_required)
            ->options($this->getDefaultGenders($this->resource))
            ->yup($yup));
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

        $section->addField(Builder::birthday('birthday')
            ->label($field->label)
            ->required($field->is_required)
            ->setAttribute('minDate', $minDateString)
            ->setAttribute('maxDate', $maxDateString)
            ->yup(
                $yup->minYear((string) $minYear, $birthdayMessage)
                    ->maxYear((string) $maxYear, $birthdayMessage)
                    ->setError('typeError', __p('validation.this_field_is_not_a_valid_data'))
            ));
    }

    protected function buildLocationField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $yup = $field->is_required ? Yup::string()->required() : Yup::string()->nullable();
        $section->addFields(
            Builder::countryState('country_iso')
                ->label(__p('localize::country.country'))
                ->valueType('array')
                ->setAttribute('countryFieldName', 'country_iso')
                ->setAttribute('stateFieldName', 'country_state_id')
                ->setAttribute('cityFieldName', 'country_city_code')
                ->required($field->is_required)
                ->yup($yup),
            //City field
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
                    'state'     => ':country_state_id',
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
