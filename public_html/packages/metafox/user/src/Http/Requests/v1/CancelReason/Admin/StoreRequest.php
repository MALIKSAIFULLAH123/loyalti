<?php

namespace MetaFox\User\Http\Requests\v1\CancelReason\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\User\Http\Controllers\Api\v1\CancelReasonAdminController::store;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phrase_var' => ['required', 'array', new TranslatableTextRule()],
            'is_active'  => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data =  parent::validated($key, $default);
        $data = Arr::add($data, 'is_active', 0);
        Arr::set($data, 'phrase_var', Language::extractPhraseData('phrase_var', $data));

        return $data;
    }
}
