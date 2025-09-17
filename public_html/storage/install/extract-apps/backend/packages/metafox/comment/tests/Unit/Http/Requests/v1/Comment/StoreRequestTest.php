<?php

namespace MetaFox\Comment\Tests\Unit\Http\Requests\v1\Comment;

use MetaFox\Comment\Http\Requests\v1\Comment\StoreRequest as Request;
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
            $this->shouldRequire('item_id', 'item_type', 'text'),
        );
    }
}
