<?php

namespace MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\LiveStreaming\Http\Controllers\Api\v1\LiveVideoController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class CreateFormRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'owner_id' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
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

        return $data;
    }
}
