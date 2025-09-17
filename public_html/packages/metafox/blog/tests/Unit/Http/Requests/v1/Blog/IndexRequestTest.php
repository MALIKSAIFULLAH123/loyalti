<?php

namespace MetaFox\Blog\Tests\Unit\Http\Requests\v1\Blog;

use MetaFox\Blog\Http\Requests\v1\Blog\IndexRequest as Request;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([
                'q'         => 'search',
                'page'      => 2,
                'limit'     => 10,
                'sort'      => 'recent',
                'sort_type' => 'desc',
                'when'      => 'this_month',
            ]),
            $this->passIf('view', 'all', 'my', 'friend', 'pending', 'feature', 'sponsor', 'my_pending'),
            $this->passIf([]),
            $this->withSampleParameters('page', 'limit', 'q', 'view'),
        );
    }
}
