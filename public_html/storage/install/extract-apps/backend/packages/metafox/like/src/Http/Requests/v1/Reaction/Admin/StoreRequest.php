<?php

namespace MetaFox\Like\Http\Requests\v1\Reaction\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Like\Http\Controllers\Api\v1\ReactionAdminController::store
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
        $isEdit = $this->isEdit();
        return [
            'title'           => ['required', 'array', new TranslatableTextRule()],
            'is_active'       => ['sometimes', 'numeric', new AllowInRule([0, 1])],
            'color'           => ['sometimes', 'string'],
            'icon_font'       => ['sometimes', 'nullable', 'string'],
            'image'           => [$isEdit ? 'sometimes' : 'required', 'array'],
            'image.status'    => ['required_with:image.temp_file', 'string', new AllowInRule([MetaFoxConstant::FILE_UPDATE_STATUS])],
            'image.temp_file' => ['required_if:image.id,0', 'numeric', 'exists:storage_files,id'],
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
        $data  = parent::validated($key, $default);
        $color = Arr::get($data, 'color');
        if ($color) {
            Arr::set($data, 'color', Str::after($color, '#'));
        }

        Arr::set($data, 'title', Language::extractPhraseData('title', $data));

        return $data;
    }

    protected function isEdit(): bool
    {
        return false;
    }
}
