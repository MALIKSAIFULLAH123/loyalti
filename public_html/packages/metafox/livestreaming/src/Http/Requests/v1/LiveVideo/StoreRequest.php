<?php

namespace MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
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
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;

    /**
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'                  => ['sometimes'], // Live stream id
            'stream_key'          => ['required', 'string'],
            'playback_ids'        => ['sometimes', 'array'],
            'owner_id'            => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'privacy'             => ['required', new PrivacyRule()],
            'text'                => ['sometimes', 'nullable', 'string'],
            'is_landscape'        => ['sometimes', 'boolean'],
            'title'               => ['sometimes', 'nullable', 'string'],
            'file'                => ['sometimes', 'array'],
            'file.temp_file'      => ['required_with:file', 'numeric', 'exists:storage_files,id'],
            'location'            => ['sometimes', 'nullable', 'array'],
            'tagged_friends'      => ['sometimes', 'array'],
            'tagged_friends.*'    => ['sometimes', 'array'],
            'tagged_friends.*.id' => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'post_to'             => ['sometimes', 'string'],
            'page_id'             => ['sometimes'],
            'group_id'            => ['sometimes'],
            'to_story'            => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'webcam_player'       => ['sometimes', 'array', 'nullable'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!empty($data['post_to']) && $data['post_to'] != 'timeline') {
            Validator::validate($data, [
                "{$data['post_to']}_id" => 'required|numeric',
            ], ['required' => __p('livestreaming::phrase.this_field_is_required'), 'numeric' => __p('livestreaming::phrase.invalid_option')]);
            $data['owner_id'] = $data["{$data['post_to']}_id"];
            $data['privacy']  = MetaFoxPrivacy::EVERYONE;
        }

        if (empty($data['owner_id'])) {
            $data = $this->handlePrivacy($data);
        }

        $data['temp_file'] = 0;
        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
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

        if (array_key_exists('webcam_player', $data)) {
            $data['webcam_player_video'] = $data['webcam_player']['video'] ?? null;
            $data['webcam_player_audio'] = $data['webcam_player']['audio'] ?? null;
            $data['type']                = 'webcam';
        }

        return $data;
    }
}
