<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\Page;

use MetaFox\Page\Http\Requests\v1\Page\IndexRequest as Request;
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
            $this->withSampleParameters(
                'q',
                'view',
                'sort',
                'sort_type',
                'category_id',
                'user_id'
            )
        );
    }
}
