<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\Saved;

use MetaFox\Saved\Http\Requests\v1\Saved\IndexRequest as Request;
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
            $this->passIf([]),
            $this->failIf('collection_id', 0, 'string', []),
            $this->failIf('type', 0, []),
            $this->failIf('open', 0, 'string', []),
            $this->withSampleParameters('sort_type', 'q', 'page', 'limit')
        );
    }
}
