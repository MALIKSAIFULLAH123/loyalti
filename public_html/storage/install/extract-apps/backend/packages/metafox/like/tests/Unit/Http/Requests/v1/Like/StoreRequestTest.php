<?php

namespace MetaFox\Like\Tests\Unit\Http\Requests\v1\Like;

use MetaFox\Like\Http\Requests\v1\Like\StoreRequest as Request;
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
            $this->shouldRequire('item_id', 'item_type'),
            $this->failIf('item_id', null, 'string'),
            $this->failIf('item_type', null, 0),
            $this->passIf([
                'item_id'   => 1,
                'item_type' => 'blog',
            ])
        );
    }
}
