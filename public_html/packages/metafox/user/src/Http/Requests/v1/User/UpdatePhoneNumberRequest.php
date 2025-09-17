<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Sms\Rules\PhoneNumberRule;

/**
 * Class UpdatePhoneNumberRequest.
 */
class UpdatePhoneNumberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.$user.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $context = user();

        return [
            'phone_number' => [
                'sometimes',
                'string',
                'nullable',
                new PhoneNumberRule(),
                Rule::unique('users', 'phone_number')->ignore($context),
            ],
            'resolution' => ['string', 'sometimes', 'nullable', new AllowInRule(['web', 'mobile'])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
