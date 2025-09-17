<?php

namespace MetaFox\Friend\Http\Requests\v1\Friend;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use MetaFox\Friend\Support\Browse\Scopes\Friend\SortScope;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Platform\Rules\PaginationLimitRule;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class MentionRequest.
 */
class MentionRequest extends IndexRequest
{
    protected function assignOwnerId(array $data): array
    {
        if (!Arr::has($data, 'owner_id')) {
            Arr::set($data, 'owner_id', 0);
        }

        return $data;
    }
}
