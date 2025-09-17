<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\GenderTrait;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxFileType;
use MetaFox\Platform\Rules\MetaFoxPasswordFormatRule;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Models\Field;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\RegexRule\Support\Facades\Regex;
use MetaFox\User\Support\User as UserSupport;
use MetaFox\Yup\StringShape;
use MetaFox\Yup\Yup;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @preload 1
 */
class UserRegisterForm extends AbstractForm
{
    use GenderTrait;

    protected ?string $code       = null;
    protected ?string $inviteCode = null;

    /**
     * @param Request $request
     *
     * @return void
     * @throws AuthorizationException
     */
    public function boot(Request $request): void
    {
        $this->code       = $request->input('code');
        $this->inviteCode = $request->input('invite_code');
    }

    protected function prepare(): void
    {
        $value = [
            'code'                   => $this->code,
            'subscribe_notification' => !Settings::get('user.enable_opt_in_agreement', false),
            'country_iso'            => Country::getDefaultCountryIso(),
        ];

        if ($this->inviteCode) {
            Arr::set($value, 'invite_code', $this->inviteCode);
        }

        $result = app('events')->dispatch('user.registration.extra_fields.values', [$value], true);

        if (!empty($result) && is_array($result)) {
            $value = array_merge($value, $result);
        }

        if (Arr::get($value, 'subscription_package_id') == null) {
            Arr::forget($value, 'subscription_package_id');
        }

        $this->title(__p('user::phrase.create_a_new_account'))
            ->action('/register')
            ->asPost()
            ->submitAction('user/register')
            ->setValue($value);
    }

    protected function getPasswordValidate(): StringShape
    {
        $passwordRule = new MetaFoxPasswordFormatRule();

        return Yup::string()
            ->required()
            ->maxLength(Settings::get('user.maximum_length_for_password', 30))
            ->minLength(Settings::get('user.minimum_length_for_password', 8))
            ->setError('required', __p('validation.this_field_is_a_required_field'))
            ->setError('typeError', __p('validation.password_is_a_required_field'))
            ->setError('minLength', __p('validation.field_must_be_at_least_min_length_characters', [
                'field'     => '${path}',
                'minLength' => '${min}',
            ]))
            ->matchesArray($passwordRule->getFormRules(), $passwordRule->message());
    }

    public function initialize(): void
    {
        $basic = $this->addBasic();

        $this->handleAvatarField($basic);
        Log::channel('profiler')->debug('filds', Builder::getFields());

        $this->handleFullNameField($basic);
        $this->handleUserNameField($basic);
        match (Settings::get('user.enable_phone_number_registration')) {
            true    => $this->handleEmailAndPhoneNumberFields($basic),
            default => $this->handleEmailFields($basic),
        };

        $this->handlePasswordFields($basic);

        $this->buildFieldInBasicInfoSection($basic);

        app('events')->dispatch('user.registration.extra_fields.build', [$basic]);

        $this->addCustomFields($basic);

        $this->buildSubscribeNotificationField($basic);
        $this->buildAgreeField($basic);

        $basic->addField(Captcha::getFormField('user.user_signup'));

        $this->buildFooter();
    }

    protected function buildFooter(): void
    {
        $footer = $this->addFooter([
            'style' => [
                'display'        => 'flex',
                'flexWrap'       => 'wrap',
                'justifyContent' => 'space-between',
            ],
        ]);

        $footer->addFields(
            Builder::submit()
                ->sizeLarge()
                ->marginDense()
                ->label(__p('user::phrase.create_account')),
            Builder::linkButton('already_had_an_account')
                ->link('/login')
                ->variant('link')
                ->sizeMedium()
                ->marginDense()
                ->color('primary')
                ->setAttribute('controlProps', [
                    'textAlign' => 'left',
                ])
                ->showWhen(['eq', 'mediaScreen', 'small'])
                ->label(__p('user::phrase.already_had_an_account')),
        );
    }

    protected function handleEmailFields(Section $basic): void
    {
        $basic->addFields(
            Builder::validateText('email')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('core::phrase.email_address'))
                ->placeholder(__p('core::phrase.email_address'))
                ->returnKeyType('next')
                ->required()
                ->shrink()
                ->validateAction('user.user.exist')
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->email(__p('validation.field_must_be_a_valid_email'))
                        ->setError('typeError', __p('validation.email_is_a_required_field')),
                ),
        );

        $this->addEmailConfirmationField($basic);
    }

    protected function handleAvatarField(Section $basic): void
    {
        if (!Settings::get('user.force_user_to_upload_on_sign_up', false)) {
            return;
        }

        $basic->addField(
            Builder::avatarUpload('user_profile')
                ->required()
                ->accept(file_type()->getMimeTypeFromType(MetaFoxFileType::PHOTO_TYPE, false))
                ->label(__p('user::phrase.profile_image'))
                ->placeholder(__p('user::phrase.profile_image'))
                ->description(__p('user::phrase.profile_image_desc'))
                ->yup(
                    Yup::object()->addProperty(
                        'base64',
                        Yup::string()->required(__p('validation.field_is_a_required_field', [
                            'field' => __p('user::phrase.profile_image'),
                        ]))
                    )
                )
        );
    }

    protected function handlePasswordFields(Section $basic): void
    {
        $basic->addFields(
            Builder::password('password')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::phrase.password'))
                ->placeholder(__p('user::phrase.password'))
                ->returnKeyType('next')
                ->required()
                ->shrink()
                ->yup($this->getPasswordValidate()),
        );

        if (!Settings::get('user.signup_repeat_password')) {
            return;
        }

        $basic->addField(
            Builder::password('password_confirmation')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::phrase.confirm_password'))
                ->placeholder(__p('user::phrase.re_enter_password'))
                ->variant('outlined')
                ->returnKeyType('next')
                ->noFeedback(true)
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->oneOf([['ref' => 'password']], __p('validation.the_password_confirmation_is_not_matched'))
                        ->minLength(Settings::get('user.minimum_length_for_password', 8))
                        ->maxLength(Settings::get('user.maximum_length_for_password', 30))
                        ->setError('required', __p('validation.this_field_is_a_required_field'))
                        ->setError('typeError', __p('validation.password_is_a_required_field')),
                )
        );
    }

    protected function addEmailConfirmationField(Section $basic): void
    {
        if (!Settings::get('user.force_user_to_reenter_email', false)) {
            return;
        }

        $field = Builder::email('reenter_email')
            ->autoComplete('off')
            ->marginNormal()
            ->label(__p('core::phrase.reenter_email_address'))
            ->placeholder(__p('core::phrase.reenter_email_address'))
            ->returnKeyType('next')
            ->required()
            ->yup(Yup::string()
                ->required()
                ->oneOf([['ref' => 'email']], __p('validation.the_reenter_information_is_not_matched'))
                ->format('email')
                ->setError('required', __p('validation.this_field_is_a_required_field'))
                ->setError('typeError', __p('validation.reenter_email_is_a_required_field'))
                ->setError('format', __p('validation.invalid_email_address')));

        $basic->addField($field);
    }

    protected function handleEmailAndPhoneNumberFields(Section $basic): void
    {
        $basic->addFields(
            Builder::validateText('email')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::web.email_or_phone'))
                ->placeholder(__p('user::web.email_or_phone'))
                ->returnKeyType('next')
                ->required()
                ->shrink()
                ->validateAction('user.user.exist')
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                ),
        );

        $this->addEmailAndPhoneNumberConfirmationField($basic);
    }

    protected function addEmailAndPhoneNumberConfirmationField(Section $basic): void
    {
        if (!Settings::get('user.force_user_to_reenter_email', false)) {
            return;
        }

        $basic->addField(
            Builder::email('reenter_email')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::phrase.reenter_email_or_phone'))
                ->placeholder(__p('user::phrase.reenter_email_or_phone'))
                ->returnKeyType('next')
                ->required()
                ->yup(
                    Yup::string()
                        ->oneOf([['ref' => 'email']], __p('validation.the_reenter_information_is_not_matched'))
                        ->required(__p('validation.this_field_is_a_required_field'))
                )
        );
    }

    protected function handleUserNameField(Section $basic): void
    {
        $setting = Settings::get('user.available_name_field_on_sign_up');

        if ($setting != UserSupport::DISPLAY_BOTH && $setting != UserSupport::DISPLAY_USER_NAME) {
            return;
        }

        $usernameRegex = Regex::getUsernameRegexSetting();

        $basic->addField(
            Builder::validateText('user_name')
                ->marginNormal()
                ->label(__p('core::phrase.username'))
                ->placeholder(__p('user::phrase.choose_a_username'))
                ->returnKeyType('next')
                ->shrink()
                ->autoComplete('off')
                ->validateAction('user.user.exist')
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->matches($usernameRegex)
                        ->minLength(Settings::get('user.min_length_for_username', 5))
                        ->maxLength(Settings::get(
                            'user.max_length_for_username',
                            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
                        ))
                        ->setError('required', __p('validation.this_field_is_a_required_field'))
                        ->setError('typeError', __p('validation.user_name_is_a_required_field'))
                        ->setError('matches', __p(Settings::get('regex.user_name_regex_error_message'))),
                )
        );
    }

    protected function handleFullNameField(Section $basic): void
    {
        $setting = Settings::get('user.available_name_field_on_sign_up');

        if ($setting != UserSupport::DISPLAY_BOTH && $setting != UserSupport::DISPLAY_FULL_NAME) {
            return;
        }

        $fullNameYup = Yup::string()
            ->required()
            ->setError('required', __p('validation.this_field_is_a_required_field'))
            ->setError('typeError', __p('validation.full_name_is_a_required_field'));

        if (Settings::get('user.validate_full_name', true)) {
            $displayNameRegex = Regex::getRegexSetting('display_name');

            $fullNameYup->maxLength(Settings::get('user.maximum_length_for_full_name'))
                ->minLength(3)
                ->matches($displayNameRegex, __p(Settings::get('regex.display_name_regex_error_message')));
        }

        $basic->addField(
            Builder::text('full_name')
                ->marginNormal()
                ->label(__p('user::phrase.display_name'))
                ->placeholder(__p('user::phrase.display_name'))
                ->returnKeyType('next')
                ->required()
                ->yup($fullNameYup),
        );
    }

    protected function addCustomFields(Section $basic): void
    {
        $settingRoleId = Settings::get('user.on_register_user_group', UserRole::NORMAL_USER);
        CustomFieldFacade::loadFieldRegistration($basic, [
            'resolution' => MetaFox::getResolution(),
            'role_id'    => $settingRoleId,
        ]);
    }

    protected function buildAgreeField(Section $section): void
    {
        if (!Settings::get('user.new_user_terms_confirmation')) {
            return;
        }

        $section->addField(
            Builder::checkbox('agree')
                ->label(__p('user::phrase.agree_field_label'))
                ->returnKeyType('next')
                ->required()
                ->uncheckedValue(false)
                ->yup(
                    Yup::number()
                        ->required()
                        ->setError('required', __p('validation.agree_field_is_a_required_field'))
                        ->setError('typeError', __p('validation.agree_field_is_a_required_field'))
                )
        );
    }

    protected function buildSubscribeNotificationField(Section $section): void
    {
        if (!Settings::get('user.enable_opt_in_agreement')) {
            return;
        }

        $section->addField(
            Builder::checkbox('subscribe_notification')
                ->label(__p('user::phrase.would_like_to_receive_notification_via_text_messages_and_emails'))
                ->returnKeyType('next')
                ->uncheckedValue(false)
        );
    }

    protected function buildFieldInBasicInfoSection(Section $sectionForm): void
    {
        $fieldCollections = $this->fieldRepository()->getFieldCollectionsByBasicInfoSection();

        /** @var Field[] $fieldCollections */
        foreach ($fieldCollections as $field) {
            if (!in_array($field->field_name, $this->allowFieldRegister())) {
                continue;
            }

            if (!$field->is_register) {
                continue;
            }

            $methodName = 'build' . Str::studly($field->field_name) . 'Field';

            $this->$methodName($field, $sectionForm);
        }
    }

    protected function buildGenderField(Field $field, Section $section, array $attributes = []): void
    {
        $yup = $field->is_required ? Yup::number()->required(__p('user::validation.gender_is_a_required_field')) : Yup::number()->nullable();

        $section->addField(
            Builder::gender('gender')
                ->label($field->label)
                ->required($field->is_required)
                ->options($this->getDefaultGenders(user()))
                ->yup($yup)
        );
    }

    protected function buildBirthdateField(Field $field, Section $section, array $attributes = []): void
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
        $yup             = $field->is_required
            ? Yup::date()->required(__p('user::validation.birthday_is_a_required_field'))
            : Yup::date()->nullable();

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
                ));
    }

    protected function buildLocationField(Field $field, Section $section, array $attributes = []): void
    {
        $yupLocationField = $field->is_required
            ? Yup::string()->required(__p('user::validation.country_is_a_required_field'))
            : Yup::string()->nullable();

        $section->addFields(
            Builder::countryState('country_iso')
                ->label(__p('localize::country.country'))
                ->valueType('array')
                ->setAttribute('countryFieldName', 'country_iso')
                ->setAttribute('stateFieldName', 'country_state_id')
                ->setAttribute('cityFieldName', 'country_city_code')
                ->required($field->is_required)
                ->yup($yupLocationField),
            Builder::countryCity('country_city_code')
                ->label(__p('localize::country.city'))
                ->description(__p('localize::country.city_name'))
                ->searchEndpoint('user/city')
                ->searchParams([
                    'country' => ':country_iso',
                    'state'   => ':country_state_id',
                ])
                ->showWhen([
                    'truthy',
                    'country_iso',
                ]),
        );
    }

    protected function fieldRepository(): FieldRepositoryInterface
    {
        return resolve(FieldRepositoryInterface::class);
    }

    protected function allowFieldRegister(): array
    {
        return ['gender', 'location', 'birthdate'];
    }
}
