<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use MetaFox\Ban\Rules\BanEmailRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\MetaFoxPasswordFormatRule;
use MetaFox\Platform\Rules\RegexRule;
use MetaFox\Platform\Rules\RegexUsernameRule;
use MetaFox\Platform\Rules\UniqueEmail;
use MetaFox\Platform\Rules\UniqueSlug;
use MetaFox\Sms\Rules\PhoneNumberRule;
use MetaFox\User\Rules\PasswordHistoryCheckRule;
use MetaFox\User\Support\User;
use stdClass;

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * @var MetaFoxPasswordFormatRule
     */
    private $passwordRule;

    /**
     * @return MetaFoxPasswordFormatRule
     */
    public function getPasswordRule(): MetaFoxPasswordFormatRule
    {
        if (!$this->passwordRule instanceof MetaFoxPasswordFormatRule) {
            $this->passwordRule = resolve(MetaFoxPasswordFormatRule::class);
        }

        return $this->passwordRule;
    }

    /**
     * @param  MetaFoxPasswordFormatRule $rule
     * @return void
     */
    public function setPasswordRule(MetaFoxPasswordFormatRule $rule): void
    {
        $this->passwordRule = $rule;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws AuthenticationException
     */
    public function rules(): array
    {
        $context = user();

        $rules = [
            'user_name' => [
                'sometimes',
                'string',
                new UniqueSlug('user', $context->id),
                new RegexUsernameRule(),
            ],
            'full_name'                 => ['sometimes', 'string', 'nullable', new RegexRule('display_name')],
            'first_name'                => ['sometimes', 'string', 'nullable'],
            'last_name'                 => ['sometimes', 'string', 'nullable'],
            'email'                     => ['sometimes', 'email', new UniqueEmail($context->id), new BanEmailRule($context->email)],
            'old_password'              => ['sometimes', 'current_password:api'],
            'new_password'              => ['required_with:old_password', $this->getPasswordRule(), new PasswordHistoryCheckRule()],
            'new_password_confirmation' => [
                'required_with:new_password', 'same:new_password',
            ],
            'language_id'  => ['sometimes', 'string', 'nullable', 'exists:core_languages,language_code,is_active,1'],
            'currency_id'  => ['sometimes', 'string', 'nullable', 'exists:core_currencies,code'],
            'phone_number' => [
                'sometimes',
                'string',
                'nullable',
                new PhoneNumberRule(),
                Rule::unique('users', 'phone_number')->ignore($context),
            ],
            'profile.language_id'    => ['sometimes', 'string', 'nullable', 'exists:core_languages,language_code,is_active,1'],
            'profile.currency_id'    => ['sometimes', 'string', 'nullable', 'exists:core_currencies,code'],
            User::THEME_TYPE_SETTING => ['sometimes', 'string', 'nullable'],
            User::THEME_ID_SETTING   => ['sometimes', 'string', 'nullable'],
            'logout_others'          => ['sometimes', 'nullable', new AllowInRule([0, 1])],
        ];

        try {
            $proxy = new stdClass();

            foreach ($rules as $key => $value) {
                $proxy->{$key} = $value;
            }

            app('events')->dispatch('user.form_request.override_update_user', [$proxy, $context]);

            $rules = (array) $proxy;
        } catch (\Throwable $exception) {
            Log::error('override update user request error: ' . $exception->getMessage());
            Log::error('override update user request error trace: ' . $exception->getTraceAsString());
        }

        return $rules;
    }

    /**
     * @param mixed $key
     * @param mixed $default
     *
     * @return array<mixed>
     */
    public function validated($key = null, $default = null)
    {
        $data    = parent::validated($key, $default);
        $context = user();

        // Only allow spaces between characters
        if (isset($data['new_password'])) {
            $data['password'] = trim($data['new_password']);
        }

        if (isset($data['language_id'])) {
            $data['profile']['language_id'] = $data['language_id'];
        }

        if (isset($data['currency_id'])) {
            $data['profile']['currency_id'] = $data['currency_id'];
        }

        app('events')->dispatch('user.validate_mfa_field_for_request', [$context, $data], true);

        $fullName = Arr::get($data, 'full_name', '');
        if (null === $fullName) {
            Arr::set($data, 'full_name', '');
        }

        return $data;
    }

    /**
     * @return array<string,string>
     */
    public function messages()
    {
        return [
            'old_password.password'          => __p('validation.current_password'),
            'new_password_confirmation.same' => __p('validation.confirmed', ['attribute' => 'new password']),
        ];
    }
}
