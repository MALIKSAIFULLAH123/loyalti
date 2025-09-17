<?php

namespace MetaFox\Friend\Http\Requests\v1\Friend;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\Rules\PaginationLimitRule;

/**
 * Class FriendBirthdaysRequest.
 */
class FriendBirthdaysRequest extends FormRequest
{
    public const DEFAULT_ITEM_PER_PAGE = 20;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'page'  => ['sometimes', 'numeric', 'min:1'],
            'month' => ['sometimes', 'numeric', 'min:1'],
            'view'  => ['sometimes', 'string'],
            'limit' => ['sometimes', 'numeric', new PaginationLimitRule()],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (!isset($data['limit'])) {
            $data['limit'] = self::DEFAULT_ITEM_PER_PAGE;
        }

        return $data;
    }
}
