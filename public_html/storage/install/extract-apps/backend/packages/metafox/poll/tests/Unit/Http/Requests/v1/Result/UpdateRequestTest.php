<?php

namespace MetaFox\Poll\Tests\Unit\Http\Requests\v1\Result;

use MetaFox\Poll\Http\Requests\v1\Result\UpdateRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Poll\Http\Controllers\Api\ResultController::$controllers;
 * stub: api_action_request_test.stub
 */

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->failIf('answers', 'string', null, 1)
        );
    }
}
