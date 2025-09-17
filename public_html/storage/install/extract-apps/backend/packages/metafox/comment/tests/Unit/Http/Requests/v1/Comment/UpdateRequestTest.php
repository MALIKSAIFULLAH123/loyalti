<?php

namespace MetaFox\Comment\Tests\Unit\Http\Requests\v1\Comment;

use MetaFox\Comment\Http\Requests\v1\Comment\UpdateRequest;
use Tests\TestFormRequest;

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return UpdateRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([]),
            $this->failIf('text', 0, []),
            $this->failIf('is_hide', null, -1, 2),
            $this->failIf('photo_id', -1)
        );
    }
}
