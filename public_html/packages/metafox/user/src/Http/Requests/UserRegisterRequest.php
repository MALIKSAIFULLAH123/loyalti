<?php

namespace MetaFox\User\Http\Requests;

use ArrayObject;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MetaFox\Ban\Rules\BanEmailRule;
use MetaFox\Captcha\Support\Facades\Captcha;
use MetaFox\Core\Support\Facades\Country;
use MetaFox\Core\Support\Facades\CountryCity as CityFacade;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Models\CountryCity;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\MetaFoxPasswordFormatRule;
use MetaFox\Platform\Rules\RegexRule;
use MetaFox\Platform\Rules\RegexUsernameRule;
use MetaFox\Platform\Rules\UniqueSlug;
use MetaFox\Platform\UserRole;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\User\Support\User as UserSupport;

/**
 * Class UserRegisterRequest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserRegisterRequest extends FormRequest
{
    public const ACTION_CAPTCHA_NAME = 'user.user_signup';

    /**
     * @var MetaFoxPasswordFormatRule
     */
    private $passwordRule;

    /**
     * @return mixed
     */
    public function getPasswordRule()
    {
        if (!$this->passwordRule instanceof MetaFoxPasswordFormatRule) {
            $this->passwordRule = resolve(MetaFoxPasswordFormatRule::class);
        }

        return $this->passwordRule;
    }

    /**
     * @param mixed $passwordRule
     */
    public function setPasswordRule($passwordRule): void
    {
        $this->passwordRule = $passwordRule;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->guest();
    }

    protected function prepareForValidation()
    {
        $this->preparePhoneNumberForValidation();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $usernameMinLength = Settings::get('user.min_length_for_username', 5);
        $usernameMaxLength = Settings::get('user.max_length_for_username', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);
        $displayField      = Settings::get('user.available_name_field_on_sign_up');

        Log::channel('dev')->info('get rules');

        $rules = new ArrayObject([
            'email'        => [
                'required_without:phone_number',
                'nullable',
                'string',
                'email',
                new BanEmailRule(),
                new CaseInsensitiveUnique('users', 'email'),
            ],
            'phone_number' => [
                'required_without:email',
                'string',
                new PhoneNumberRule(),
                new CaseInsensitiveUnique('users', 'phone_number'),
            ],
            'password'     => ['required', 'string', $this->getPasswordRule()],
        ]);

        if ($displayField == UserSupport::DISPLAY_BOTH || $displayField == UserSupport::DISPLAY_FULL_NAME) {
            $fullNameRule = ['required', 'string'];

            if (Settings::get('user.validate_full_name', true)) {
                $maxLengthFullName = Settings::get('user.maximum_length_for_full_name');

                $fullNameRule = [
                    ...$fullNameRule,
                    'min:3',
                    'max:' . $maxLengthFullName,
                    new RegexRule('display_name'),
                ];
            }

            $rules['full_name'] = $fullNameRule;
        }

        if ($displayField == UserSupport::DISPLAY_BOTH || $displayField == UserSupport::DISPLAY_USER_NAME) {
            $rules['user_name'] = [
                'string',
                'required',
                'min:' . $usernameMinLength,
                'max:' . $usernameMaxLength,
                new UniqueSlug('user'),
                new RegexUsernameRule(),
            ];
        }

        if (Settings::get('user.signup_repeat_password')) {
            $rules['password_confirmation'] = ['required_with:password', 'string', 'same:password'];
        }

        if (Settings::get('user.force_user_to_reenter_email', false)) {
            $rules['reenter_email']        = ['required_without:phone_number', 'nullable', 'string', 'same:email'];
            $rules['reenter_phone_number'] = ['required_without:email', 'nullable', 'string', 'same:phone_number'];
        }

        if (Settings::get('user.new_user_terms_confirmation', true)) {
            $rules['agree'] = ['required', 'accepted'];
        }

        $rules['custom_gender'] = [
            'required_if:gender,0', 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:user_gender,id'),
        ];

        $rules['captcha'] = Captcha::ruleOf(self::ACTION_CAPTCHA_NAME);

        app('events')->dispatch('user.registration.extra_field.rules', [$rules]);

        $roleId                = Settings::get('user.on_register_user_group', UserRole::NORMAL_USER);
        $subscriptionPackageId = $this->request->get('subscription_package_id');

        if ($subscriptionPackageId) {
            $roleId = app('events')->dispatch('subscription.get_role_package_by_id', [$subscriptionPackageId]);
        }

        CustomFieldFacade::loadFieldRegistrationRules($rules, [
            'role_id' => $roleId,
        ]);

        $rules['subscribe_notification'] = ['sometimes', 'nullable', 'boolean'];

        Log::channel('dev')->debug('validation_rules', $rules->getArrayCopy());

        return $rules->getArrayCopy();
    }

    /**
     * @throws AuthenticationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        // Only allow spaces between characters
        $data['password']       = trim($data['password']);
        $data['approve_status'] = MetaFoxConstant::STATUS_APPROVED;

        $data = $this->transformCustomFields($data);
        $this->transformCountryState($data);
        $this->transformCityCode($data);
        $this->transformUserLanguage($data);
        $this->transformUserName($data);

        $this->handleBasicProfileFields($data);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        $messages = [
            'agree.accepted'             => __p('validation.required', ['attribute' => 'agree']),
            'password.required'          => __p('validation.password_field_validation_required'),
            'password_confirmation.same' => __p('validation.the_password_confirmation_is_not_matched'),
            'reenter_email.same'         => __p('validation.the_reenter_information_is_not_matched'),
            'reenter_phone_number.same'  => __p('validation.the_reenter_information_is_not_matched'),
            'custom_gender.required_if'  => __p('validation.the_custom_gender_field_is_a_required_field'),
        ];

        if (Settings::get('user.enable_phone_number_registration')) {
            $messages['email.email'] = __p('validation.invalid_email_or_phone');
        }

        $extraMessages = app('events')->dispatch('user.registration.extra_field.rule_messages');

        if (is_array($extraMessages)) {
            foreach ($extraMessages as $extraMessage) {
                if (is_array($extraMessage)) {
                    $messages = array_merge($messages, $extraMessage);
                }
            }
        }

        $params = $this->getQueryParamsCustomFields();
        $result = CustomFieldFacade::handleFieldValidationErrorMessage(Auth::user(), $params);

        return array_merge($messages, $result);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function transformCityCode(array &$data): void
    {
        $cityCode = Arr::get($data, 'country_city_code') ?? 0;

        if (is_array($cityCode)) {
            $cityCode = Arr::get($cityCode, 'value') ?? 0;
        }

        $data['country_city_code'] = $cityCode;
        $city                      = CityFacade::getCity($cityCode);
        $data['city_location']     = $city instanceof CountryCity ? $city->name : '';
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function handleBasicProfileFields(array &$data): void
    {
        $this->handleGenderField($data);
        $fields = $this->getBasicProfileFields();

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data['profile'][$field] = $data[$field];
                unset($data[$field]);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function handleGenderField(array &$data): void
    {
        $gender = Arr::get($data, 'gender') ?? 0;

        $customGender = Arr::get($data, 'custom_gender') ?? 0;

        $data['gender_id'] = max($gender, $customGender);

        if ($gender > 0) {
            $data['gender_id'] = $gender;
        }
    }

    /**
     * @return array<string>
     */
    protected function getBasicProfileFields(): array
    {
        return [
            'country_state_id', 'country_city_code', 'country_iso',
            'birthday', 'city_location', 'gender_id', 'language_id',
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function transformCountryState(mixed &$data)
    {
        if (!Arr::has($data, 'country_iso')) {
            Arr::set($data, 'country_iso', Country::getDefaultCountryIso());
        }

        $countryStateId = Arr::get($data, 'country_state_id') ?? 0;
        if ($countryStateId) {
            return;
        }

        $countryState = Arr::get($data, 'country_state') ?? 0;
        if (is_array($countryState)) {
            $countryStateId = Arr::get($countryState, 'value') ?? 0;
        }

        $data['country_state_id'] = $countryStateId;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function transformUserLanguage(array &$data)
    {
        $locale           = Arr::get($data, 'language_id', App::getLocale());
        $availableLocales = Language::availableLocales();

        if (empty($locale) || !in_array($locale, $availableLocales)) {
            $locale = Language::getDefaultLocaleId();
        }

        $data['language_id'] = $locale;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    protected function transformUserName(array &$data): void
    {
        $userName = Arr::get($data, 'user_name');
        if ($userName) {
            return;
        }

        $data['user_name'] = sprintf(UserSupport::GENERATED_USER_NAME, Carbon::now()->timestamp, Str::random());
    }

    private function preparePhoneNumberForValidation()
    {
        if (!Settings::get('user.enable_phone_number_registration')) {
            return;
        }

        $email = $this->input('email');

        // override the phone_number if the email matches the phone number format
        $emailIsPhoneNumber = Validator::make([
            'phone_number' => $email,
        ], [
            'phone_number' => new PhoneNumberRule(),
        ])->passes();

        if (!$emailIsPhoneNumber) {
            return;
        }

        $this->merge([
            'phone_number' => $email,
            'email'        => null,
        ]);

        if (Settings::get('user.force_user_to_reenter_email', false)) {
            $this->merge([
                'reenter_phone_number' => $this->input('reenter_email'),
                'reenter_email'        => null,
            ]);
        }
    }

    private function transformCustomFields(array $data): array
    {
        $roleId                = Settings::get('user.on_register_user_group', UserRole::NORMAL_USER);
        $subscriptionPackageId = Arr::get($data, 'subscription_package_id');

        if ($subscriptionPackageId) {
            $roleId = app('events')->dispatch('subscription.get_role_package_by_id', [$subscriptionPackageId]);
        }
        $params = $this->getQueryParamsCustomFields();

        Arr::set($params, 'role_id', $roleId);

        return CustomFieldFacade::handleCustomProfileFieldsForEdit(Auth::user(), $data, $params);
    }

    private function getQueryParamsCustomFields(): array
    {
        return [
            'section_type' => CustomField::SECTION_TYPE_USER,
            'view'         => CustomField::VIEW_REGISTRATION,
        ];
    }
}
