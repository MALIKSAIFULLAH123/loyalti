<?php

namespace MetaFox\Event\Tests\Unit\Http\Requests\v1\Category\Admin;

use MetaFox\Event\Http\Requests\v1\Category\Admin\IndexRequest as Request;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 * @group category.admin
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
            $this->withSampleParameters('page', 'limit'),
            $this->failIf('parent_id', [], 'string'),
        );
    }
}
