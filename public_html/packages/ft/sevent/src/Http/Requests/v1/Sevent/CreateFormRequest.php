<?php

namespace Foxexpert\Sevent\Http\Requests\v1\Sevent;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \Foxexpert\Sevent\Http\Controllers\Api\v1\SeventController::createForm;
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
            'course_id'  => ['nullable', 'numeric']
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        if (!empty($data['owner_id'])) {
            $data['owner_id'] = (int) $data['owner_id'];
        }

        if (!empty($data['course_id'])) {
            $data['course_id'] = (int) $data['course_id'];
        }

        return $data;
    }
}
