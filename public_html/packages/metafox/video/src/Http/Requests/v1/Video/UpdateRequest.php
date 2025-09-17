<?php

namespace MetaFox\Video\Http\Requests\v1\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Video\Support\Facade\Video as VideoFacade;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Video\Http\Controllers\Api\v1\VideoController::update;
 * stub: api_action_request.stub
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
    public function rules(): array
    {
        $rules = [
            'title'               => ['sometimes', 'string', new ResourceNameRule('video')],
            'text'                => ['sometimes', 'string', 'nullable'],
            'thumbnail'           => ['sometimes', 'array'],
            'thumbnail.temp_file' => [
                'required_if:file.status,' . MetaFoxConstant::FILE_UPDATE_STATUS, 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'thumbnail.status' => ['required_with:file', 'string', new AllowInRule([MetaFoxConstant::FILE_UPDATE_STATUS, MetaFoxConstant::FILE_REMOVE_STATUS])],
            'categories'       => ['sometimes', 'array'],
            'categories.*'     => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class), $this->route('video'))],
            'owner_id'         => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'album'            => [
                'sometimes', 'nullable', 'numeric', new ExistIfGreaterThanZero('exists:photo_albums,id'),
            ],
            'privacy' => ['required', new PrivacyRule([
                'validate_privacy_list' => false,
            ])],
        ];

        return $this->handleMatureContent($rules);
    }

    private function handleMatureContent(array $rules): array
    {
        $context = user();

        if (!$context->hasPermissionTo('video.add_mature_video')) {
            return $rules;
        }

        $rules['mature'] = ['sometimes', 'nullable', 'numeric', new AllowInRule(VideoFacade::getAllowMatureContent())];

        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data['thumb_temp_file']  = Arr::get($data, 'thumbnail.temp_file', 0);
        $data['remove_thumbnail'] = Arr::get($data, 'thumbnail.status', false);

        if (isset($data['album'])) {
            $data['album_id'] = $data['album'];
        }

        return $data;
    }
}
