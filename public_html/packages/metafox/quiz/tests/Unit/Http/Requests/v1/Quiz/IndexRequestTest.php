<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Requests\v1\Quiz;

use MetaFox\Quiz\Http\Requests\v1\Quiz\IndexRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Quiz\Http\Controllers\Api\QuizController::$controllers;
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
            $this->withSampleParameters('user_id', 'when', 'sort', 'sort_type', 'view', 'q'),
        );
    }
}
