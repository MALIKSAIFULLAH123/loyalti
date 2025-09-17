<?php

namespace MetaFox\User\Http\Resources\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Form\AbstractField;
use MetaFox\Form\GenderTrait;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Form\Section as SectionForm;
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
 * @driverType form-mobile
 * @driverName user.register
 * @preload    1
 */
class UserRegisterMobileForm extends AbstractForm
{
    use GenderTrait;

    protected ?string $code       = null;
    protected ?string $inviteCode = null;

    /**
     * @param Request $request
     *
     * @return void
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

        $value = app('events')->dispatch('user.registration.extra_fields.values', [$value], true) ?? $value;

        if (Arr::get($value, 'subscription_package_id') == null) {
            Arr::forget($value, 'subscription_package_id');
        }

        $this->title(__p('user::phrase.create_account'))
            ->action('/register')
            ->asPost()
            ->submitAction('user/register')
            ->setValue($value);
    }

    /**
     * @throws AuthenticationException
     */
    public function initialize(): void
    {
        $basic = $this->addBasic();

        if (Settings::get('user.force_user_to_upload_on_sign_up', false)) {
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

        $this->handleFullNameField($basic);
        $this->handleUserNameField($basic);
        match (Settings::get('user.enable_phone_number_registration')) {
            true    => $this->handleEmailAndPhoneNumberFields($basic),
            default => $this->handleEmailFields($basic),
        };

        $basic->addField(
            Builder::password('password')
                ->autoComplete('off')
                ->marginNormal()
                ->label(__p('user::phrase.password'))
                ->placeholder(__p('user::phrase.password'))
                ->returnKeyType('next')
                ->required()
                ->minLength(Settings::get('user.minimum_length_for_password', 8))
                ->yup($this->getPasswordValidate()),
        );

        if (Settings::get('user.signup_repeat_password')) {
            $basic->addFields(
                Builder::password('password_confirmation')
                    ->autoComplete('off')
                    ->marginNormal()
                    ->label(__p('user::phrase.confirm_password'))
                    ->placeholder(__p('user::phrase.confirm_password'))
                    ->returnKeyType('next')
                    ->noFeedback(true)
                    ->required()
                    ->minLength(Settings::get('user.minimum_length_for_password', 8))
                    ->yup(
                        Yup::string()
                            ->required()
                            ->oneOf([['ref' => 'password']], __p('validation.the_password_confirmation_is_not_matched'))
                            ->minLength(Settings::get('user.minimum_length_for_password', 8))
                            ->maxLength(Settings::get('user.maximum_length_for_password', 30))
                            ->setError('required', __p('validation.this_field_is_a_required_field'))
                            ->setError('typeError', __p('validation.password_is_a_required_field')),
                    ),
            );
        }

        $this->buildFieldInBasicInfoSection($basic);

        app('events')->dispatch('user.registration.extra_fields.build', [$basic]);

        $this->addCustomFields($basic);

        $this->getFieldSubscribeNotify($basic);

        if (Settings::get('user.new_user_terms_confirmation')) {
            $basic->addField(
                Builder::checkbox('agree')
                    ->label(__p('user::phrase.agree_field_label_embed', [
                        'term_of_use_link' => 'term-of-use',
                        'policy_link'      => 'policy',
                    ]))
                    ->setAttribute('isReverse', true)
                    ->variant('standard-inlined-end')
                    ->returnKeyType('next')
                    ->required()
                    ->yup(
                        Yup::number()
                            ->required()
                            ->setError('required', __p('validation.agree_field_is_a_required_field'))
                            ->setError('typeError', __p('validation.agree_field_is_a_required_field'))
                    )
            );
        }

        $footer = $this->addFooter([
            'variant' => 'horizontal',
            'style'   => [
                'justifyContent' => 'space-between',
            ],
        ]);

        $footer->addFields(
            Builder::submit()
                ->sizeLarge()
                ->marginDense()
                ->label(__p('user::phrase.create_account')),
            Builder::linkButton()
                ->link('/login')
                ->marginDense()
                ->fullWidth()
                ->variant('standard')
                ->sizeLarge()
                ->label(__p('user::phrase.already_had_an_account'))
        );
    }

    /**
     * @throws AuthenticationException
     */
    public function initializeFlatten(): void
    {
        $basic = $this->addBasic();

        if (Settings::get('user.force_user_to_upload_on_sign_up', false)) {
            $basic->addField(
                Builder::avatarUpload('user_profile')
                    ->variant('standard-outlined')
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

        $this->handleFullNameFieldFlatten($basic);
        $this->handleUserNameFieldFlatten($basic);
        match (Settings::get('user.enable_phone_number_registration')) {
            true    => $this->handleEmailAndPhoneNumberFieldsFlatten($basic),
            default => $this->handleEmailFieldsFlatten($basic),
        };

        $basic->addField(
            Builder::password('password')
                ->autoComplete('off')
                ->marginNone()
                ->variant('standard-outlined')
                ->label(__p('user::phrase.password'))
                ->placeholder(__p('user::phrase.password'))
                ->returnKeyType('next')
                ->required()
                ->yup($this->getPasswordValidate()),
        );

        if (Settings::get('user.signup_repeat_password')) {
            $basic->addFields(
                Builder::password('password_confirmation')
                    ->autoComplete('off')
                    ->variant('standard-outlined')
                    ->label(__p('user::phrase.confirm_password'))
                    ->placeholder(__p('user::phrase.confirm_password'))
                    ->returnKeyType('next')
                    ->noFeedback(true)
                    ->required()
                    ->yup(
                        Yup::string()
                            ->required()
                            ->minLength(Settings::get('user.minimum_length_for_password', 8))
                            ->maxLength(Settings::get('user.maximum_length_for_password', 30))
                            ->setError('required', __p('validation.this_field_is_a_required_field'))
                            ->setError('typeError', __p('validation.password_is_a_required_field')),
                    ),
            );
        }

        $this->buildFieldInBasicInfoSection($basic);

        app('events')->dispatch('user.registration.extra_fields.build', [$basic]);

        $this->addCustomFields($basic);
        $this->getFieldSubscribeNotify($basic);

        if (Settings::get('user.new_user_terms_confirmation')) {
            $basic->addField(
                Builder::checkbox('agree')
                    ->label(__p('user::phrase.agree_field_label_embed', [
                        'term_of_use_link' => 'term-of-use',
                        'policy_link'      => 'policy',
                    ]))
                    ->setAttribute('isReverse', true)
                    ->variant('standard-inlined-end')
                    ->returnKeyType('next')
                    ->required()
                    ->yup(
                        Yup::number()
                            ->required()
                            ->setError('required', __p('validation.agree_field_is_a_required_field'))
                            ->setError('typeError', __p('validation.agree_field_is_a_required_field'))
                    )
            );
        }

        $footer = $this->addFooter([
            'variant' => 'horizontal',
            'style'   => [
                'justifyContent' => 'space-between',
            ],
        ]);

        $footer->addFields(
            Builder::submit()
                ->sizeLarge()
                ->marginDense()
                ->label(__p('user::phrase.create_account')),
            Builder::linkButton()
                ->link('/login')
                ->marginDense()
                ->fullWidth()
                ->variant('standard')
                ->sizeLarge()
                ->label(__p('user::phrase.already_had_an_account'))
        );
    }

    protected function getPasswordValidate(): StringShape
    {
        $passwordValidate = Yup::string()
            ->required()
            ->minLength(Settings::get('user.minimum_length_for_password', 8))
            ->maxLength(Settings::get('user.maximum_length_for_password', 30))
            ->setError('required', __p('validation.this_field_is_a_required_field'))
            ->setError('typeError', __p('validation.password_is_a_required_field'))
            ->setError('minLength', __p('validation.field_must_be_at_least_min_length_characters', [
                'field'     => '${path}',
                'minLength' => '${min}',
            ]));

        $passwordRule = new MetaFoxPasswordFormatRule();

        $passwordValidate->matchesArray($passwordRule->getFormRules(), $passwordRule->message());

        return $passwordValidate;
    }

    protected function handleEmailFields(Section $basic): void
    {
        $basic->addFields(
            Builder::email('email')
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

    protected function addEmailConfirmationField(Section $basic): void
    {
        if (!Settings::get('user.force_user_to_reenter_email', false)) {
            return;
        }

        $basic->addFields(
            Builder::email('reenter_email')
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
                    ->setError('format', __p('validation.invalid_email_address')))
        );
    }

    protected function handleEmailAndPhoneNumberFields(Section $basic): void
    {
        $basic->addFields(
            Builder::email('email')
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

        $basic->addField(
            $this->getUserNameField()->marginNormal()
        );
    }

    protected function handleFullNameField(Section $basic): void
    {
        $setting = Settings::get('user.available_name_field_on_sign_up');

        if ($setting != UserSupport::DISPLAY_BOTH && $setting != UserSupport::DISPLAY_FULL_NAME) {
            return;
        }

        $basic->addField(
            $this->getFullNameField()
                ->marginNormal(),
        );
    }

    protected function addEmailConfirmationFieldFlatten(Section $basic): void
    {
        if (!Settings::get('user.force_user_to_reenter_email', false)) {
            return;
        }

        $basic->addFields(
            Builder::email('reenter_email')
                ->autoComplete('off')
                ->marginNone()
                ->variant('standard-outlined')
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
                    ->setError('format', __p('validation.invalid_email_address')))
        );
    }

    protected function handleEmailFieldsFlatten(Section $basic): void
    {
        $basic->addFields(
            Builder::email('email')
                ->autoComplete('off')
                ->marginNone()
                ->variant('standard-outlined')
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

        $this->addEmailConfirmationFieldFlatten($basic);
    }

    protected function handleEmailAndPhoneNumberFieldsFlatten(Section $basic): void
    {
        $basic->addFields(
            Builder::email('email')
                ->autoComplete('off')
                ->marginNone()
                ->variant('standard-outlined')
                ->label(__p('user::web.email_or_phone'))
                ->placeholder(__p('user::web.email_or_phone'))
                ->returnKeyType('next')
                ->validateAction('user.user.exist')
                ->required()
                ->shrink()
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field')),
                ),
        );

        $this->addEmailAndPhoneNumberConfirmationFieldFlatten($basic);
    }

    protected function addEmailAndPhoneNumberConfirmationFieldFlatten(Section $basic): void
    {
        if (!Settings::get('user.force_user_to_reenter_email', false)) {
            return;
        }

        $basic->addField(
            Builder::email('reenter_email')
                ->autoComplete('off')
                ->marginNone()
                ->variant('standard-outlined')
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

    protected function handleUserNameFieldFlatten(Section $basic): void
    {
        $setting = Settings::get('user.available_name_field_on_sign_up');

        if ($setting != UserSupport::DISPLAY_BOTH && $setting != UserSupport::DISPLAY_USER_NAME) {
            return;
        }

        $basic->addField($this->getUserNameField()
            ->marginNone()
            ->variant('standard-outlined'));
    }

    protected function handleFullNameFieldFlatten(Section $basic): void
    {
        $setting = Settings::get('user.available_name_field_on_sign_up');

        if ($setting != UserSupport::DISPLAY_BOTH && $setting != UserSupport::DISPLAY_FULL_NAME) {
            return;
        }

        $basic->addField(
            $this->getFullNameField()
                ->marginNone()
                ->variant('standard-outlined'),
        );
    }

    protected function getFullNameField(): AbstractField
    {
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

        return Builder::text('full_name')
            ->label(__p('user::phrase.display_name'))
            ->placeholder(__p('user::phrase.display_name'))
            ->returnKeyType('next')
            ->required()
            ->yup($fullNameYup);
    }

    protected function getUserNameField(): AbstractField
    {
        $usernameRegex = Regex::getUsernameRegexSetting();

        return Builder::text('user_name')
            ->label(__p('core::phrase.username'))
            ->placeholder(__p('user::phrase.choose_a_username'))
            ->returnKeyType('next')
            ->autoComplete('off')
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
                    ->setError('matches', __p(Settings::get('regex.user_name_regex_error_message')))
                    ->setError('minLength', __p('validation.field_must_be_at_least_min_length_characters', [
                        'field'     => '${path}',
                        'minLength' => '${min}',
                    ])),
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

    protected function getFieldSubscribeNotify(Section $basic): void
    {
        if (Settings::get('user.enable_opt_in_agreement')) {
            $basic->addField(
                Builder::checkbox('subscribe_notification')
                    ->label(__p('user::phrase.would_like_to_receive_notification_via_text_messages_and_emails'))
                    ->variant('standard-inlined-end')
                    ->returnKeyType('next')
                    ->setAttribute('isReverse', true)
            );
        }
    }

    protected function buildFieldInBasicInfoSection(SectionForm $sectionForm): void
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

    protected function buildGenderField(Field $field, SectionForm $section, array $attributes = []): void
    {
        $yup = $field->is_required ? Yup::number()->required() : Yup::number()->nullable();

        $section->addField(
            Builder::choice('gender')
                ->label($field->label)
                ->required($field->is_required)
                ->enableSearch()
                ->options($this->getDefaultGenders(user()))
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
                ));
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
                ->required($field->is_required)
                ->yup($yup),
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
                    'country' => ':country_iso',
                    'state'   => ':country_state',
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
