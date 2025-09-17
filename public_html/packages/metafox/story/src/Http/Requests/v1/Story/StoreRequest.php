<?php

namespace MetaFox\Story\Http\Requests\v1\Story;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\Platform\Rules\PrivacyRule;
use MetaFox\Platform\Rules\ResourceTextRule;
use MetaFox\Platform\Traits\Http\Request\PrivacyRequestTrait;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Story\Support\StorySupport;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Story\Http\Controllers\Api\v1\StoryController::store
 * stub: /packages/requests/api_action_request.stub
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
    public function rules()
    {
        $settings    = Settings::get('story.lifespan_options', StorySupport::LIFESPAN_VALUE_OPTIONS);
        $maxLifespan = Arr::last(Arr::sort($settings), null, StorySupport::USER_STORY_LIFESPAN);

        $rules = [
            'privacy'              => ['required', new PrivacyRule()],
            'lifespan'             => ['sometimes', 'int', 'min:1', 'max:' . $maxLifespan],
            'text'                 => ['sometimes', 'string', new ResourceTextRule(true)],
            'font_style'           => ['sometimes', 'string'],
            'expand_link'          => ['sometimes', 'nullable', 'string', 'url'],
            'background_id'        => ['sometimes', 'int', 'exists:story_backgrounds,id'],
            'type'                 => [
                'sometimes', 'string', new AllowInRule([
                    StorySupport::STORY_TYPE_PHOTO,
                    StorySupport::STORY_TYPE_TEXT,
                    StorySupport::STORY_TYPE_VIDEO,
                    StorySupport::STORY_TYPE_SHARE,
                ]),
            ],
            'file'                 => ['sometimes', 'array'],
            'file.temp_file'       => ['required_with:file', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'thumb_file'           => ['sometimes', 'array'],
            'thumb_file.temp_file' => ['required_with:thumb_file', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'duration'             => ['sometimes', 'numeric', 'max:' . StoryFacades::getConfiguredVideoDuration()],
        ];

        $this->handleExtraRules($rules);
        return $rules;
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        $data = $this->handlePrivacy($data);

        if (isset($data['file']['temp_file'])) {
            $data['temp_file'] = $data['file']['temp_file'];
        }

        if (!Arr::has($data, 'type')) {
            Arr::set($data, 'type', StorySupport::STORY_TYPE_PHOTO);
        }

        if (!Arr::has($data, 'duration')) {
            $duration = match ($data['type']) {
                StorySupport::STORY_TYPE_VIDEO => StoryFacades::getConfiguredVideoDuration(),
                default                        => StorySupport::STORY_DURATION_DEFAULT,
            };

            Arr::set($data, 'duration', $duration);
        }

        if (Arr::has($data, 'expand_link')) {
            Arr::set($data, 'extra.expand_link', Arr::pull($data, 'expand_link'));
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function messages(): array
    {
        return [
            'duration.max' => __p('story::web.you_can_not_upload_videos_longer_than', [
                'value' => StoryFacades::getConfiguredVideoDuration(),
            ]),
        ];
    }

    protected function handleExtraRules(array &$rules): void
    {
        $rules = array_merge($rules, [
            'extra'                                 => ['sometimes', 'array'],
            'extra.isBrowser'                       => ['sometimes', 'boolean'],
            'extra.storyHeight'                     => ['sometimes', 'nullable', 'numeric'],
            'extra.size'                            => ['sometimes', 'array'],
            'extra.size.height'                     => ['sometimes', 'nullable', 'numeric'],
            'extra.size.width'                      => ['sometimes', 'nullable', 'numeric'],
            'extra.transform'                       => ['sometimes', 'array'],
            'extra.transform.rotation'              => ['sometimes', 'nullable', 'numeric'],
            'extra.transform.scale'                 => ['sometimes', 'nullable', 'numeric'],
            'extra.transform.position'              => ['sometimes', 'array'],
            'extra.transform.position.top'          => ['sometimes', 'nullable', 'string'],
            'extra.transform.position.left'         => ['sometimes', 'nullable', 'string'],
            'extra.texts'                           => ['sometimes', 'array'],
            'extra.texts.*'                         => ['sometimes', 'array'],
            'extra.texts.*.text'                    => ['sometimes', 'nullable', 'string'],
            'extra.texts.*.color'                   => ['sometimes', 'nullable', 'string'],
            'extra.texts.*.fontFamily'              => ['sometimes', 'nullable', 'string'],
            'extra.texts.*.fontSize'                => ['sometimes', 'nullable', 'numeric'],
            'extra.texts.*.width'                   => ['sometimes', 'nullable', 'string'],
            'extra.texts.*.textAlign'               => ['sometimes', 'nullable', 'string'],
            'extra.texts.*.id'                      => ['sometimes', 'nullable', 'string'],
            'extra.texts.*.transform'               => ['sometimes', 'nullable', 'array'],
            'extra.texts.*.transform.rotation'      => ['sometimes', 'nullable', 'numeric'],
            'extra.texts.*.transform.scale'         => ['sometimes', 'nullable', 'numeric'],
            'extra.texts.*.transform.position'      => ['sometimes', 'nullable', 'array'],
            'extra.texts.*.transform.position.top'  => ['sometimes', 'nullable', 'string'],
            'extra.texts.*.transform.position.left' => ['sometimes', 'nullable', 'string'],
        ]);
    }
    /**
     * =========<Example Rules>=========
     *
     * {
     * extra: ExtraPayload,
     * expand_link?: string;
     * background_id?: string (only type=text)
     * lifespan: number;
     * privacy: number;
     * type: 'photo' | 'text' | 'video'
     * duration?: number (only type=video)
     * file?: {
     * temp_file?: number,
     * type: 'photo'
     * } (only type=photo or video)
     * thumb_file: {
     * temp_file?: number,
     * type: 'photo'
     * } (required)
     * }
     *
     * TranformType(Object):{
     * position: {
     * top: number | string // (ex: 10% | 0),
     * left: number | string // (ex: 10% | 0),
     * }
     * rotation: number;
     * scale: number
     * }
     *
     * ExtraPayload (Object):
     * {
     * isBrowser: boolean;
     * size: {
     * height: number,
     * width: number
     * };
     * storyHeight: number
     * transform: TranformType;
     * texts: Array<TextsPayload>
     * }
     *
     * TextsPayload (Object):
     * {
     * color: string;
     * fontFamily: string;
     * fontSize: number;
     * text: string;
     * width: string;
     * transform: TranformType;
     * textAlign?: string;
     * id?: string;
     * }
     */
}
