<?php

namespace MetaFox\Video\Tests\Unit\Http\Requests\v1\Category;

use MetaFox\Video\Http\Requests\v1\Category\IndexRequest as Request;
use Tests\TestCase;
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
            $this->passIf([]),
            $this->withSampleParameters('page', 'limit', 'q'),
            $this->failIf('level', [], 'string'),
        );
    }
}
