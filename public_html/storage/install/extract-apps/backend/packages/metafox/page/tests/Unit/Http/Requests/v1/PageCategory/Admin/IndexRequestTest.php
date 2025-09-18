<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageCategory\Admin;

use MetaFox\Page\Http\Requests\v1\PageCategory\Admin\IndexRequest as Request;
use Tests\TestCase;
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
            $this->withSampleParameters('page', 'limit', 'q'),
            $this->failIf('parent_id', 0, [], 'string'),
            $this->failIf('level', [], 'string'),
        );
    }
}
