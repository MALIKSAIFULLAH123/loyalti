<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Question;

use MetaFox\Group\Http\Requests\v1\Question\IndexRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
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
            $this->shouldRequire('group_id', ),
            $this->withSampleParameters('page'),
            $this->failIf('group_id', null, 'string', 0),
        );
    }
}
