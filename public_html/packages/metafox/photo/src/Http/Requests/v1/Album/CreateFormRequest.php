<?php

namespace MetaFox\Photo\Http\Requests\v1\Album;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Photo\Http\Controllers\Api\v1\AlbumController::createForm;
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class CreateFormRequest.
 */
class CreateFormRequest extends FormRequest
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
        $data = parent::validated();

        if (!array_key_exists('owner_id', $data)) {
            $data['owner_id'] = 0;
        }

        if (array_key_exists('owner_id', $data)) {
            $data['owner_id'] = (int) $data['owner_id'];
        }

        return $data;
    }
}
