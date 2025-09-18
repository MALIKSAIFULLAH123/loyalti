<?php

namespace MetaFox\Music\Http\Requests\v1\Playlist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Traits\Http\Request\AttachmentRequestTrait;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Music\Http\Controllers\Api\v1\PlaylistController::update;
 * stub: api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
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
            'thumbnail.temp_file' => [
                'required_if:file.status,update', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'thumbnail.status'    => ['required_with:file', 'string', new AllowInRule([MetaFoxConstant::FILE_UPDATE_STATUS, MetaFoxConstant::FILE_REMOVE_STATUS])],
            'owner_id'            => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'privacy'             => ['required', new PrivacyRule()],
        ];

        $rules = $this->applyAttachmentRules($rules);

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data['thumb_temp_file']  = Arr::get($data, 'thumbnail.temp_file', 0);
        $data['remove_thumbnail'] = Arr::get($data, 'thumbnail.status', false);

        return $data;
    }
}
