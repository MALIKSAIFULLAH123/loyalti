<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\Saved;

use MetaFox\Saved\Http\Requests\v1\Saved\UnSaveRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class UnSaveRequestTest.
 */
class UnSaveRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('item_id', 'item_type'),
            $this->failIf('item_id', null, 'string', []),
            $this->failIf('item_type', null, 0, []),
            $this->passIf('item_id', 0),
            $this->passIf('item_type', 'any string'),
        );
    }
}
