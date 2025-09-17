<?php

namespace MetaFox\Giphy\Http\Requests\v1\Gif;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Giphy\Supports\Helpers;
use MetaFox\Platform\Rules\AllowInRule;

class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'      => ['required', 'string'],
            'limit'  => ['sometimes', 'numeric'],
            'rating' => ['sometimes', 'string', new AllowInRule(Helpers::GIPHY_RATINGS)],
            'lang'   => ['sometimes', 'string', new AllowInRule(Helpers::GIPHY_LANGUAGES)],
            'bundle' => ['sometimes', 'string', new AllowInRule(Helpers::GIPHY_BUNDLES)],
            'page'   => ['sometimes', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'limit')) {
            Arr::set($data, 'limit', Helpers::DEFAULT_LIMIT);
        }

        if (Arr::has($data, 'page')) {
            $offset = (Arr::get($data, 'page') - 1) * Arr::get($data, 'limit') + 1;
            Arr::set($data, 'offset', $offset);
        } else {
            Arr::set($data, 'offset', Helpers::DEFAULT_OFFSET);
        }

        return $data;
    }
}
