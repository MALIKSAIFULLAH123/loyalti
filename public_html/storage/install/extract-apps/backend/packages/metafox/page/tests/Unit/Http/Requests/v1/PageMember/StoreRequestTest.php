<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageMember;

use MetaFox\Page\Http\Requests\v1\PageMember\StoreRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('page_id'),
            $this->failIf('page_id', 0, null, 'string'),
        );
    }
}
