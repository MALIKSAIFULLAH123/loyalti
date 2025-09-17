<?php

namespace MetaFox\SEO\Http\Requests\v1\Meta\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Core\Http\Controllers\Api\v1\MetaAdminController::update;
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
        return [
            'title'           => [new TranslatableTextRule()],
            'heading'         => [new TranslatableTextRule(true)],
            'keywords'        => [new TranslatableTextRule(true)],
            'description'     => [new TranslatableTextRule(true)],
            'canonical_url'   => ['url', 'nullable'],
            'robots_no_index' => ['numeric', 'nullable', new AllowInRule([1, 0])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        Arr::set($data, 'title', Language::extractPhraseData('title', $data));
        Arr::set($data, 'heading', Language::extractPhraseData('heading', $data));
        Arr::set($data, 'keywords', Language::extractPhraseData('keywords', $data));
        Arr::set($data, 'description', Language::extractPhraseData('description', $data));

        return $data;
    }
}
