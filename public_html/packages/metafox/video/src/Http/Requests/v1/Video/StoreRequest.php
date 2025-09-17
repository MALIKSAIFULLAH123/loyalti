<?php

namespace MetaFox\Video\Http\Requests\v1\Video;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\CategoryRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceNameRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Video\Support\Facade\Video;
use MetaFox\Video\Support\Facade\Video as VideoFacade;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Video\Http\Controllers\Api\v1\VideoController::store;
 * stub: api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
class StoreRequest extends FormRequest
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
            'title'               => ['required', 'string', new ResourceNameRule('video')],
            'text'                => ['sometimes', 'string', 'nullable'],
            'file'                => ['required_without:video_url'],
            'file.temp_file'      => ['required_with:file', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'thumbnail'           => ['sometimes', 'array'],
            'thumbnail.temp_file' => ['required_with:thumbnail', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'video_url'           => ['required_without:file', 'url', 'nullable', 'exclude_with:file'],
            'categories'          => ['sometimes', 'array'],
            'categories.*'        => ['numeric', new CategoryRule(resolve(CategoryRepositoryInterface::class))],
            'owner_id'            => ['sometimes', 'numeric', new ExistIfGreaterThanZero('exists:user_entities,id')],
            'privacy'             => ['required', new PrivacyRule()],
            'is_posted_from_feed' => ['sometimes', 'numeric', new AllowInRule([0, 1])],
        ];

        $rules = $this->handleMatureContent($rules);

        return $rules;
    }

    /**
     * @throws ValidationException
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data = $this->handlePrivacy($data);

        $data                    = Arr::add($data, 'is_posted_from_feed', 0);
        $data['temp_file']       = Arr::get($data, 'file.temp_file', 0);
        $data['thumb_temp_file'] = Arr::get($data, 'thumbnail.temp_file', 0);

        if (empty($data['owner_id'])) {
            $data['owner_id'] = 0;
        }

        $videoUrl = $data['video_url'] ?? null;
        if ($videoUrl) {
            $linkData = $this->validateLink($videoUrl);
            Arr::set($data, 'video_url', $linkData['video_url'] ?? $videoUrl);
            $data = array_merge($linkData, $data);
        }

        return $data;
    }

    /**
     * @param string|null $url
     * @return array<string, mixed>
     * @throws ValidationException
     */
    protected function validateLink(?string $url): array
    {
        if (!$url) {
            return [];
        }

        $data = Video::parseLink($url);

        // The parsed link should be a valid video link
        $isVideo = $data['is_video'] ?? false;
        Validator::make(
            ['video_url' => $isVideo],   //data
            ['video_url' => 'accepted'], //rules
            ['accepted' => __p('video::phrase.unsupported_link_with_providers')] //error messages
        )->validated();

        return [
            'title'      => $data['title'] ?? null,
            'text'       => $data['description'] ?? null,
            'embed_code' => $data['embed_code'] ?? null,
            'duration'   => $data['duration'] ?? null,
            'thumbnail'  => $data['image'] ?? null,
            'is_file'    => $data['is_file'] ?? false,
            'in_process' => 0,
            'video_url'  => $data['link'] ?? $url,
        ];
    }

    protected function handleMatureContent(array $rules): array
    {
        try {
            $context = user();

            if (!$context->hasPermissionTo('video.add_mature_video')) {
                return $rules;
            }
        } catch (\Exception $exception) {
        }

        $rules['mature'] = ['sometimes', 'nullable', 'numeric', new AllowInRule(VideoFacade::getAllowMatureContent())];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required_without'      => __p('video::validation.video_file_is_a_required_field'),
            'video_url.required_without' => __p('video::validation.invalid_video_link'),
            'video_url.url'              => __p('video::validation.invalid_video_link'),
        ];
    }
}
