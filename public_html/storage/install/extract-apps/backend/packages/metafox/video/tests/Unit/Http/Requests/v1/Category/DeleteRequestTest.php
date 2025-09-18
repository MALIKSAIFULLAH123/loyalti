<?php

namespace MetaFox\Video\Tests\Unit\Http\Requests\v1\Category;

use MetaFox\Video\Http\Requests\v1\Category\DeleteRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class DeleteRequestTest.
 */
class DeleteRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('migrate_items'),
            $this->failIf('migrate_items', 2, null, [], 'string'),
            $this->passIf('migrate_items', 0, 1),
            $this->failIf('new_category_id', 0, null, [], 'string')
        );
    }
}
