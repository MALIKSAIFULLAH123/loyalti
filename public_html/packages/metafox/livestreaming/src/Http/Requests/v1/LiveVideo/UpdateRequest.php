<?php

namespace MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\LiveStreaming\Http\Controllers\Api\v1\LiveVideoController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    use PrivacyRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'playback_ids'        => ['sometimes', 'array'],
            'status'              => ['sometimes', 'nullable', 'string'],
            'file'                => ['sometimes', 'array'],
            'file.temp_file'      => [
                'required_if:file.status,update', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'file.status'         => ['required_with:file', 'string', new AllowInRule(['update', 'remove'])],
            'duration'            => ['sometimes', 'nullable', 'numeric'],
            'text'                => ['sometimes', 'nullable', 'string'],
            'title'               => ['sometimes', 'nullable', 'string'],
            'privacy'             => ['sometimes', new PrivacyRule([
                'validate_privacy_list' => false,
            ])],
            'location'            => ['sometimes', 'nullable', 'array'],
            'tagged_friends'      => ['sometimes', 'array'],
            'tagged_friends.*'    => ['sometimes', 'array'],
            'tagged_friends.*.id' => ['sometimes', 'integer', new ExistIfGreaterThanZero('exists:user_entities,id')],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (isset($data['privacy'])) {
            $data = $this->handlePrivacy($data);
        }

        $data['temp_file'] = 0;
        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
        }

        $data['remove_image'] = false;
        if (isset($data['file']['status'])) {
            $data['remove_image'] = true;
        }

        if (array_key_exists('location', $data)) {
            $data['location_name']      = $data['location']['address'] ?? null;
            $data['location_latitude']  = $data['location']['lat'] ?? null;
            $data['location_longitude'] = $data['location']['lng'] ?? null;
            $data['location_address']   = $data['location']['full_address'] ?? null;
            unset($data['location']);
        }

        if (array_key_exists('tagged_friends', $data)) {
            $tagged                 = Arr::get($data, 'tagged_friends', []);
            $data['tagged_friends'] = collect($tagged)->pluck('id')->toArray();
        }

        return $data;
    }
}
