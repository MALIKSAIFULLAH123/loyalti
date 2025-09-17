<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Form\GenderTrait;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\MetaFoxPasswordFormatRule;
use MetaFox\Platform\UserRole;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Models\UserProfile;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Traits\MfaFieldTrait;
use MetaFox\Yup\StringShape;
use MetaFox\Yup\Yup;

/**
 * Class AccountInfoSettingForm.
 *
 * @property Model $resource
 * @driverType form
 * @driverName user.update.basic_info
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AccountInfoSettingForm extends AbstractForm
{
    use GenderTrait;
    use MfaFieldTrait;

    public function boot(int $id, UserRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $user    = $this->resource;
        $profile = $this->resource->profile;
        $gender  = $profile->gender;

        $cityCode = $this->getCityCode($profile);

        $values = array_merge(
            [
                'full_name'         => $user->display_name,
                'user_name'         => $user->user_name,
                'email'             => $user->email,
                'birthday'          => $profile->birthday,
                'postal_code'       => $profile->postal_code,
                'country_city_code' => is_array($cityCode) ? Arr::get($cityCode, 'value') : null,
                'gender'            => (int) $profile->gender?->entityId(),
                'custom_gender'     => $gender?->is_custom ? $profile->gender?->entityId() : null,
                'country_iso'       => $profile->country_iso,
                'country_state_id'  => $profile->country_state_id,
                'language_id'       => $profile->language_id,
                'address'           => $profile->address,
                'phone_number'      => $user->phone_number,
            ],
            $this->getDefaultValue()
        );

        if ($this->hasRoleField()) {
            Arr::set($values, 'role_id', $user->roleId());
        }

        $this->action('admincp/user/' . $this->resource->id)
            ->resetFormOnSuccess(false)
            ->reloadFormWhenSuccess(true)
            ->asPatch()
            ->setValue($values);
    }

    public function initialize(): void
    {
        $context         = user();
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

        $this->title(__p('core::phrase.edit'));

        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('full_name')
                ->required()
                ->label(__p('user::phrase.display_name'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::text('user_name')
                ->required()
                ->marginNormal()
                ->label(__p('core::phrase.username'))
                ->yup(
                    Yup::string()
                        ->required()
                ),
            Builder::password('password')
                ->marginNormal()
                ->label(__p('core::phrase.password'))
                ->yup($this->getPasswordValidate()),
            $this->getEmailField(),
            $this->getPhoneField(),
            $this->buildRoleField(),
            Builder::countryState('country_iso')
                ->valueType('array')
                ->setAttribute('countryFieldName', 'country_iso')
                ->setAttribute('stateFieldName', 'country_state_id')
                ->setAttribute('cityFieldName', 'country_city_code'),
            //City field
            Builder::countryCity('country_city_code')
                ->label(__p('localize::country.city'))
                ->showWhen([
                    'truthy',
                    'country_iso',
                ])
                ->description(__p('localize::country.city_name'))
                ->searchEndpoint('user/city')
                ->searchParams([
                    'country'   => ':country_iso',
                    'state'     => ':country_state_id',
                    'city_code' => ':country_city_code',
                ]),
            //Address field
            Builder::text('address')
                ->label(__p('user::phrase.address')),
            Builder::text('postal_code')
                ->label(__p('user::phrase.zip_postal_code'))
                ->placeholder('- - - - - -'),
            Builder::gender('gender')
                ->label(__p('user::phrase.user_gender'))
                ->options($this->getDefaultGenders($context)),
            Builder::birthday('birthday')
                ->label(__p('user::phrase.birthday'))
                ->setAttribute('minDate', $minDateString)
                ->setAttribute('maxDate', $maxDateString)
                ->yup(
                    Yup::date()
                        ->nullable(true)
                        ->minYear((string) $minYear, $birthdayMessage)
                        ->maxYear((string) $maxYear, $birthdayMessage)
                        ->setError('typeError', __p('core::phrase.invalid_date'))
                ),
            Builder::choice('language_id')
                ->marginNormal()
                ->label(__p('core::phrase.primary_language'))
                ->placeholder(__p('core::phrase.primary_language'))
                ->autoComplete('off')
                ->required()
                ->options(Language::getActiveOptions())
                ->yup(Yup::string()->required()),
        );

        $this->buildProfilePictureField($basic);

        $this->addDefaultFooter(true);
    }

    private function getDefaultValue(): array
    {
        $default           = [];
        $default['avatar'] = ['url' => $this->resource->profile->avatar];

        return $default;
    }

    private function buildProfilePictureField(Section $basic)
    {
        $basic->addField(
            Builder::typography('profile_picture_typo')
                ->variant('h5')
                ->plainText(__p('user::phrase.profile_picture'))
        );

        $basic->addField(
            Builder::avatarUpload('avatar')
                ->label(__p('user::phrase.profile_image'))
                ->placeholder(__p('user::phrase.profile_image'))
                ->description(__p('user::phrase.profile_image_desc'))
                ->yup(
                    Yup::object()->addProperty(
                        'base64',
                        Yup::string()
                    )->nullable()
                )
        );
    }

    protected function buildRoleField(): ?FormField
    {
        if (!$this->hasRoleField()) {
            return null;
        }

        $roleOptions = resolve(RoleRepositoryInterface::class)->getRoleOptions();

        if (!user()->hasSuperAdminRole()) {
            $roleOptions = array_filter($roleOptions, function ($role) {
                return Arr::get($role, 'value') != UserRole::SUPER_ADMIN_USER;
            });
        }

        $roleOptions = array_values($roleOptions);
        $isDisable   = false;
        if (UserFacade::isBan($this->resource->entityId())) {
            $roleOptions = array_merge($roleOptions, [
                ['value' => UserRole::BANNED_USER,
                 'label' => __p('user::phrase.banned_user'),],
            ]);
            $isDisable   = true;
        }

        return Builder::choice('role_id')
            ->multiple(false)
            ->disableClearable()
            ->label(__p('core::phrase.role'))
            ->options($roleOptions)
            ->disabled($isDisable)
            ->yup(
                Yup::number()
                    ->positive()
                    ->required()
            );
    }

    protected function hasRoleField(): bool
    {
        return $this->resource && !$this->resource->hasSuperAdminRole();
    }

    public function getCityCode(?UserProfile $profile): ?array
    {
        if (!$profile->country_city_code) {
            return null;
        }

        return [
            'label' => $profile->city_location,
            'value' => $profile->country_city_code,
        ];
    }

    protected function getPasswordValidate(): StringShape
    {
        $passwordRule = new MetaFoxPasswordFormatRule();

        return Yup::string()
            ->setError('typeError', __p('validation.password_is_a_required_field'))
            ->setError('minLength', __p('validation.field_must_be_at_least_min_length_characters', [
                'field'     => '${path}',
                'minLength' => '${min}',
            ]))
            ->minLength(Settings::get('user.minimum_length_for_password', 8))
            ->maxLength(Settings::get('user.maximum_length_for_password', 30))
            ->matchesArray($passwordRule->getFormRules(), $passwordRule->message());
    }

    protected function getEmailField(): AbstractField
    {
        $emailField = Builder::text('email')
            ->marginNormal()
            ->label(__p('core::phrase.email_address'))
            ->yup(
                Yup::string()
                    ->email(__p('validation.invalid_email_address'))
            );

        $this->applyMfaRequiredEmailField($emailField);

        return $emailField;
    }

    protected function getPhoneField(): AbstractField
    {
        $phoneField = Builder::phoneNumber('phone_number')
            ->marginNormal();

        $this->applyMfaRequiredPhoneField($phoneField);

        return $phoneField;
    }
}
