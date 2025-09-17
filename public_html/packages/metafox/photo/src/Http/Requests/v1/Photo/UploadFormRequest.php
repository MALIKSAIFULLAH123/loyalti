<?php

namespace MetaFox\Photo\Http\Requests\v1\Photo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Photo\Http\Controllers\Api\v1\PhotoController::uploadForm;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UploadFormRequest.
 */
class UploadFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'owner_id' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['owner_id'] = Arr::get($data, 'owner_id', 0);

        return $data;
    }
}
