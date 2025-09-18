<?php

namespace MetaFox\GettingStarted\Http\Requests\v1\TodoList\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\GettingStarted\Rules\MaxItemResolutionRule;
use MetaFox\GettingStarted\Support\Helper;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title'      => ['required', 'array', new TranslatableTextRule()],
            'text'       => ['required', 'array', new TranslatableTextRule()],
            'resolution' => [
                'required', 'string',
                new AllowInRule([MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE]),
                new MaxItemResolutionRule(),
            ],
            'attached_photos'      => ['sometimes', 'array', 'max:' . Helper::MAX_FILES],
            'attached_photos.*.id' => [
                'required_if:attached_photos.*.status,update,remove', 'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'attached_photos.*.status' => [
                'required_with:attached_photos', new AllowInRule([
                    MetaFoxConstant::FILE_REMOVE_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS, MetaFoxConstant::FILE_NEW_STATUS,
                ]),
            ],
            'attached_photos.*.temp_file' => [
                'required_if:attached_photos.*.status,create', 'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'attached_photos.*.file_type' => [
                'required_if:attached_photos.*.status,create', 'string', new AllowInRule(
                    ['photo'],
                    __p('getting-started::phrase.the_attached_photos_are_invalid')
                ),
            ],
            'attached_photos.*.ordering' => ['sometimes', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'title', Language::extractPhraseData('title', $data));
        Arr::set($data, 'text', Language::extractPhraseData('text', $data));

        if (empty($data['resolution'])) {
            $data['resolution'] = MetaFoxConstant::RESOLUTION_WEB;
        }

        return $data;
    }
}
