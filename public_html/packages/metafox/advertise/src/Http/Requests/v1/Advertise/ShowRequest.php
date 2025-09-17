<?php

namespace MetaFox\Advertise\Http\Requests\v1\Advertise;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Advertise\Support\Support;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Advertise\Http\Controllers\Api\v1\AdvertiseController::index
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class IndexRequest.
 */
class ShowRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'placement_id' => ['required', 'numeric', 'exists:advertise_placements,id'],
            'location'     => ['sometimes', 'string', new AllowInRule(Facade::getAllowedLocations())],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'location')) {
            Arr::set($data, 'location', Support::LOCATION_MAIN);
        }

        return $data;
    }
}
