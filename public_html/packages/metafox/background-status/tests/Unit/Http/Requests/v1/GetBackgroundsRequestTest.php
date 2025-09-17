<?php

namespace MetaFox\BackgroundStatus\Tests\Unit\Http\Requests\v1;

use MetaFox\BackgroundStatus\Http\Requests\v1\GetBackgroundsRequest;
use Tests\TestFormRequest;

/**
 * Class GetBackgroundsRequestTest.
 */
class GetBackgroundsRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return GetBackgroundsRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('collection_id'),
            $this->failIf('collection_id', 'string', null, 0),
            $this->withSampleParameters('page', 'limit')
        );
    }
}
