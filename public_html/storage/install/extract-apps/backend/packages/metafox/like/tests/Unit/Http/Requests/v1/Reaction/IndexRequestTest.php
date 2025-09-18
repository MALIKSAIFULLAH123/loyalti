<?php

namespace MetaFox\Like\Tests\Unit\Http\Requests\v1\Reaction;

use MetaFox\Like\Http\Requests\v1\Reaction\IndexRequest as Request;
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
            $this->passIf([])->shouldHaveResult(['limit' => 20]),
            $this->withSampleParameters('page', 'limit')
        );
    }
}
