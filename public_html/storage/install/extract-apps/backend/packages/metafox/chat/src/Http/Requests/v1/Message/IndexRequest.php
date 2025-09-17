<?php

namespace MetaFox\Chat\Http\Requests\v1\Message;

use Illuminate\Foundation\Http\FormRequest;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Helper\Pagination;
use Illuminate\Support\Str;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q'               => ['sometimes', 'nullable', 'string'],
            'message_id'      => ['sometimes', 'nullable', 'numeric', 'exists:chat_messages,id'],
            'room_id'         => ['required', 'numeric', 'exists:chat_rooms,id'],
            'last_message_id' => ['sometimes', 'numeric'],
            'scroll'          => ['sometimes', 'nullable', 'string', new AllowInRule(['up', 'down', 'all'])],
            'upper_bound'     => ['sometimes', 'nullable', 'numeric', 'min:1'],
            'lower_bound'     => ['sometimes', 'nullable', 'numeric', 'min:1'],
            'page'            => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'           => ['sometimes', 'nullable', 'integer', new PaginationLimitRule()],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (!isset($data['limit'])) {
            $data['limit'] = Pagination::DEFAULT_ITEM_PER_PAGE;
        }

        if (!array_key_exists('q', $data)) {
            $data['q'] = MetaFoxConstant::EMPTY_STRING;
        }

        // Search with only whitespaces shall works like search with empty string
        $data['q'] = trim($data['q']);

        if (Str::startsWith($data['q'], '#')) {
            $data['tag'] = Str::of($data['q'])->replace('#', '')->trim();
            $data['q']   = MetaFoxConstant::EMPTY_STRING;
        }

        if (!isset($data['last_message_id'])) {
            $data['last_message_id'] = null;
        }

        if (!isset($data['message_id'])) {
            $data['message_id'] = null;
        }

        if (!isset($data['scroll'])) {
            $data['scroll'] = null;
        }

        if (!isset($data['upper_bound'])) {
            $data['upper_bound'] = null;
        }

        if (!isset($data['lower_bound'])) {
            $data['lower_bound'] = null;
        }

        return $data;
    }
}
