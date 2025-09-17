<?php

namespace MetaFox\Activity\Http\Requests\v1\Feed;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Platform\Rules\AllowInRule;

/**
 * --------------------------------------------------------------------------
 *  Http request for api version v1
 * --------------------------------------------------------------------------.
 *
 * This class is used by automatic dependency injection:
 */

/**
 * Class UpdateRequest.
 */
class DeclinePendingRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_block_author'   => ['sometimes', new AllowInRule([0, 1])],
            'delete_activities' => ['sometimes', new AllowInRule([0, 1])],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!Arr::get($data, 'is_block_author')) {
            Arr::set($data, 'is_block_author', 0);
        }

        return $data;
    }
}
