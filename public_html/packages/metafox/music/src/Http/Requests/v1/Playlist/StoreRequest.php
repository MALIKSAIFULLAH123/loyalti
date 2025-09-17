<?php

namespace MetaFox\Music\Http\Requests\v1\Playlist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Music\Http\Controllers\Api\v1\PlaylistController::store;
 * stub: api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
{
    use PrivacyRequestTrait;
    use AttachmentRequestTrait;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name'                => ['required', 'string', new ResourceNameRule('music.music_playlist')],
            'description'         => ['sometimes', 'string', 'nullable'],
            'thumbnail'           => ['sometimes', 'array'],
            'thumbnail.temp_file' => ['required_with:thumbnail', 'numeric', 'exists:storage_files,id'],
            'owner_id'            => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'privacy'             => ['required', new PrivacyRule()],
        ];

        $rules = $this->applyAttachmentRules($rules);

        return $rules;
    }

    /**
     * @throws ValidationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data['thumb_temp_file'] = Arr::get($data, 'thumbnail.temp_file', 0);

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        return $data;
    }
}
