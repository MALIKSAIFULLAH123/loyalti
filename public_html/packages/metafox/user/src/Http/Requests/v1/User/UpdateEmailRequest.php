<?php

namespace MetaFox\User\Http\Requests\v1\User;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Ban\Rules\BanEmailRule;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\UniqueEmail;

/**
 * Class UpdateEmailRequest.
 */
class UpdateEmailRequest extends FormRequest
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
            'email'      => ['sometimes', 'email', new UniqueEmail($context->id), new BanEmailRule($context->email)],
            'resolution' => ['string', 'sometimes', 'nullable', new AllowInRule(['web', 'mobile'])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        return parent::validated($key, $default);
    }
}
