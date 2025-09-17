<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Activity\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Rules\AllowInRule;

class CheckNewRequest extends FormRequest
{
    // todo risky because, it should return unxepected error.
    public function rules(): array
    {
        return [
            'last_feed_id'           => ['sometimes', 'numeric', 'exists:activity_feeds,id'],
            'last_pin_feed_id'       => ['sometimes', 'numeric', 'exists:activity_pins,feed_id'],
            'last_sponsored_feed_id' => ['sometimes', 'numeric', 'exists:activity_feeds,id'],
            'sort'                   => ['sometimes', 'string', new AllowInRule(SortScope::getAllowSort())],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!Arr::has($data, 'last_pin_feed_id')) {
            Arr::set($data, 'last_pin_feed_id', 0);
        }

        if (!Arr::has($data, 'last_feed_id')) {
            Arr::set($data, 'last_feed_id', 0);
        }

        if (!Arr::has($data, 'last_sponsored_feed_id')) {
            Arr::set($data, 'last_sponsored_feed_id', 0);
        }

        return $data;
    }
}
