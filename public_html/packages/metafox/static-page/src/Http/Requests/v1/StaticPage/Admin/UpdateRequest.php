<?php

namespace MetaFox\StaticPage\Http\Requests\v1\StaticPage\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\UniqueSlug;
use MetaFox\StaticPage\Models\StaticPage;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\StaticPage\Http\Controllers\Api\v1\StaticPageAdminController::update
 * stub: /packages/requests/api_action_request.stub
 */

/**
 * Class UpdateRequest.
 */
class UpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('page');

        return [
            'slug' => [
                'string',
                'required',
                'regex:/' . MetaFoxConstant::SLUGIFY_REGEX . '/',
                new UniqueSlug(StaticPage::ENTITY_TYPE, $id),
            ],
            'title' => ['required', 'array', new TranslatableTextRule()],
            'text'  => ['required', 'array', new TranslatableTextRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data    = parent::validated($key, $default);

        Arr::set($data, 'title', Language::extractPhraseData('title', $data));
        Arr::set($data, 'text', Language::extractPhraseData('text', $data));

        return $data;
    }
}
