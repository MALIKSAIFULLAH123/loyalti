<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Requests\v1\Quiz;

use MetaFox\Quiz\Http\Requests\v1\Quiz\CreateFormRequest;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Quiz\Http\Controllers\Api\v1\QuizController::createForm()
 * stub: /packages/requests/api_action_request_test.stub
 */

/**
 * Class CreateFormRequestTest.
 */
class CreateFormRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return CreateFormRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->passIf([])
                ->shouldHaveResult(['owner_id' => 0]),
            $this->withSampleParameters('owner_id'),
        );
    }

    public function beforeTest()
    {
        $user = $this->createNormalUser();
        $this->be($user);
    }
}
