<?php

namespace MetaFox\Poll\Tests\Unit\Http\Requests\v1\Poll;

use MetaFox\Poll\Http\Requests\v1\Poll\CreateFormRequest as Request;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Poll\Http\Controllers\Api\v1\PollController::createForm()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class CreateFormRequestTest.
 */
class CreateFormRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([])->shouldHaveResult(['owner_id' => 0]),
            $this->failIf('owner_id', 'string', ['a'])
        );
    }
}
