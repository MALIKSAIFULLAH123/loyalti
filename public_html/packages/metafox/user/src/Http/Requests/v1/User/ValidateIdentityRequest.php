<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use MetaFox\Ban\Rules\BanEmailRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CaseInsensitiveUnique;
use MetaFox\Platform\Rules\RegexUsernameRule;
use MetaFox\Platform\Rules\UniqueSlug;
use MetaFox\Sms\Rules\PhoneNumberRule;

/**
 * Class ValidateIdentityRequest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ValidateIdentityRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $email = $this->input('email');

        // override the phone_number if the email matches the phone number format
        $emailIsPhoneNumber = Validator::make([
            'phone_number' => $email,
        ], [
            'phone_number' => new PhoneNumberRule(),
        ])->passes();

        if ($emailIsPhoneNumber) {
            $this->merge([
                'phone_number' => $email,
                'email'        => '',
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            $this->getEmailRules(),
            $this->getPhoneNumberRules(),
            $this->getUsernameRules(),
            ['check_exist' => ['sometimes', 'numeric', 'nullable', new AllowInRule([0, 1])]],
        );
    }

    protected function getEmailRules(): array
    {
        $rules = [
            'required_without_all:user_name,phone_number',
            'string',
            'email',
            new BanEmailRule(),
        ];

        $rules = $this->addIdentityRule($rules, 'email');

        return ['email' => $rules];
    }

    protected function getPhoneNumberRules(): array
    {
        $rules = [
            'required_without_all:user_name,email',
            'string',
            new PhoneNumberRule(),
        ];

        $rules = $this->addIdentityRule($rules, 'phone_number');

        return ['phone_number' => $rules];
    }

    protected function getUsernameRules(): array
    {
        $rules = [
            'required_without_all:email,phone_number',
            'string',
            new UniqueSlug('user'),
            new RegexUsernameRule(),
        ];

        return ['user_name' => $rules];
    }

    protected function addIdentityRule(array $rules, string $field): array
    {
        if (request()->get('check_exist')) {
            $rules[] = new CaseInsensitiveUnique('users', $field);
        }

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated();
    }

    public function messages(): array
    {
        return [
            'email.email' => __p('validation.invalid_email_or_phone'),
        ];
    }
}
