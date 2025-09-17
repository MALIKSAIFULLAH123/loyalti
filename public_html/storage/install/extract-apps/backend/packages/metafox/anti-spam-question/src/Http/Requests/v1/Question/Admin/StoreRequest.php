<?php

namespace MetaFox\AntiSpamQuestion\Http\Requests\v1\Question\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\AntiSpamQuestion\Http\Controllers\Api\v1\QuestionAdminController::store
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class StoreRequest
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
            'question'           => ['required', 'array', new TranslatableTextRule()],
            'is_active'          => ['sometimes', 'boolean'],
            'is_case_sensitive'  => ['sometimes', 'boolean'],
            'answers'            => ['required', 'array', 'min:1'],
            'answers.*'          => ['required', 'array'],
            'answers.*.value'    => ['required', 'string', 'max:' . MetaFoxConstant::CHARACTER_LIMIT],
            'answers.*.status'   => ['required', 'string', new AllowInRule([
                MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS,
                MetaFoxConstant::FILE_REMOVE_STATUS,
            ])],
            'answers.*.ordering' => ['sometimes', 'integer'],
            'file'               => ['sometimes', 'array'],
            'file.temp_file'     => ['required_with:file', 'numeric', new ExistIfGreaterThanZero('exists:storage_files,id')],
            'file.status'        => ['required_with:file', 'string', new AllowInRule([
                MetaFoxConstant::FILE_NEW_STATUS, MetaFoxConstant::FILE_UPDATE_STATUS,
                MetaFoxConstant::FILE_REMOVE_STATUS,
            ])],
        ];
    }

    /**
     * @param        $key
     * @param        $default
     *
     * @return mixed
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'question', Language::extractPhraseData('question', $data));

        return $data;
    }

}
