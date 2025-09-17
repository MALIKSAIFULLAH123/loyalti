<?php

namespace MetaFox\Comment\Tests\Unit\Http\Requests\v1\Comment;

use MetaFox\Comment\Http\Requests\v1\Comment\HideRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class HideRequestTest.
 */
class HideRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('comment_id', 'is_hidden'),
            $this->failIf('is_hidden', 2, null),
            $this->failIf('comment_id', 0, null),
        );
    }
}
