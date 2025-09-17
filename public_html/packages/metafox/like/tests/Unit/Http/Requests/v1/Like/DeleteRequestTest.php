<?php

namespace MetaFox\Like\Tests\Unit\Http\Requests\v1\Like;

use MetaFox\Like\Http\Requests\v1\Like\DeleteRequest;
use Tests\TestFormRequest;

/**
 * Class DeleteRequestTest.
 */
class DeleteRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return DeleteRequest::class;
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
