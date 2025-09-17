<?php

namespace MetaFox\Layout\Http\Requests\v1\Variant\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ValidImageRule;
use MetaFox\Sms\Rules\PhoneNumberRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Layout\Http\Controllers\Api\v1\VariantAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $data = $this->all();

        if (!Arr::get($data, 'thumbnail.temp_file')) {
            Arr::forget($data, 'thumbnail');
        }

        $this->replace($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title'               => ['string', 'required'],
            'is_active'           => ['boolean', 'sometimes', 'nullable'],
            'is_default'          => ['boolean', 'sometimes', 'nullable'],
            'thumbnail'           => ['sometimes', 'nullable', 'array', resolve(ValidImageRule::class, ['isRequired' => true])],
            'thumbnail.status'    => ['required_with:image.temp_file', 'string', new AllowInRule([MetaFoxConstant::FILE_UPDATE_STATUS])],
            'thumbnail.temp_file' => ['required_if:image.id,0', 'numeric', 'exists:storage_files,id'],
        ];
    }
}
