<?php

namespace MetaFox\BackgroundStatus\Http\Requests\v1\BgsCollection\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\BackgroundStatus\Support\Support;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\BackgroundStatus\Http\Controllers\Api\v1\BgsCollectionAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest.
 */
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
            'title'                                        => ['required', 'array', new TranslatableTextRule()],
            'is_active'                                    => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'is_default'                                   => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'background_temp_file'                         => ['sometimes', 'array'],
            'background_temp_file.*.id'                    => [
                'required_if:background_temp_file.*.status,' . implode(',', [MetaFoxConstant::FILE_REMOVE_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS]),
                'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'background_temp_file.*.status'                => [
                'required_with:background_temp_file', new AllowInRule([
                    MetaFoxConstant::FILE_REMOVE_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS,
                    MetaFoxConstant::FILE_NEW_STATUS,
                ]),
            ],
            'background_temp_file.*.temp_file'             => [
                'required_if:background_temp_file.*.status,' . MetaFoxConstant::FILE_NEW_STATUS, 'numeric',
                new ExistIfGreaterThanZero('exists:storage_files,id'),
            ],
            'background_temp_file.*.file_type'             => [
                'required_if:background_temp_file.*.status,' . MetaFoxConstant::FILE_NEW_STATUS, 'string', new AllowInRule(['photo']),
            ],
            'background_temp_file.*.ordering'              => ['required', 'numeric'],
            'background_temp_file.*.extra_info.text_color' => ['sometimes', 'nullable', 'string', new AllowInRule(Support::getAllowColor())],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'title', Language::extractPhraseData('title', $data));

        return $data;
    }
}
