<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Group;

use MetaFox\Group\Http\Requests\v1\Group\IndexRequest;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return IndexRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([]),
            $this->withSampleParameters('view', 'sort', 'sort_type', 'when', 'category_id', 'user_id', 'page', 'limit'),
            $this->passIf('sort', 'latest', 'recent', 'feature', 'most_member')
        );
    }
}
