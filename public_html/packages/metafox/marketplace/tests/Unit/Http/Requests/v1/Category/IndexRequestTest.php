<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Requests\v1\Category;

use MetaFox\Marketplace\Http\Requests\v1\Category\IndexRequest as Request;
use Tests\TestFormRequest;

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
        );
    }
}
