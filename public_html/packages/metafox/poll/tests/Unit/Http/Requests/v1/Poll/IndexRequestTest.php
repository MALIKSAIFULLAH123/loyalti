<?php

namespace MetaFox\Poll\Tests\Unit\Http\Requests\v1\Poll;

use MetaFox\Poll\Http\Requests\v1\Poll\IndexRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Poll\Http\Controllers\Api\PollController::$controllers;
 * stub: api_action_request_test.stub
 */

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
            $this->withSampleParameters('q', 'view', 'page', 'limit', 'sort', 'sort_type', 'when', 'user_id'),
            $this->passIf('sort', 'recent')
        );
    }
}
