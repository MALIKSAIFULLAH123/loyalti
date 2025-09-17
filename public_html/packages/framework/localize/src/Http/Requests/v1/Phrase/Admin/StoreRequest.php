<?php

namespace MetaFox\Localize\Http\Requests\v1\Phrase\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Localize\Rules\TranslatableTextRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 *
 * @link \MetaFox\Localize\Http\Controllers\Api\v1\PhraseAdminController::store;
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
    public function rules(): array
    {
        return [
            'name' => ['required', 'array', new TranslatableTextRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $default = [
            'name'        => uniqid('phrase_'),
            'namespace'   => 'localize',
            'group'       => 'translatable',
            'package_id'  => 'metafox/localize',
            'is_modified' => 1,
        ];

        $data  = parent::validated($key, $default);
        $names = Language::extractPhraseData('name', $data);

        $phrases = [];
        foreach ($names as $locale => $text) {
            $phrases[] = array_merge($default, [
                'locale' => $locale,
                'text'   => $text,
            ]);
        }

        return $phrases;
    }
}
