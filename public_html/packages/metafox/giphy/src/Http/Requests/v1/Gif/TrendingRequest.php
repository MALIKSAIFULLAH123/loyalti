<?php

namespace MetaFox\Giphy\Http\Requests\v1\Gif;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Giphy\Supports\Helpers;
use MetaFox\Platform\Rules\AllowInRule;

class TrendingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'      => ['sometimes', 'string'],
            'limit'  => ['sometimes', 'numeric'],
            'rating' => ['sometimes', 'string', new AllowInRule(Helpers::GIPHY_RATINGS)],
            'bundle' => ['sometimes', 'string', new AllowInRule(Helpers::GIPHY_BUNDLES)],
            'page'   => ['sometimes', 'numeric'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!array_key_exists('limit', $data)) {
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
