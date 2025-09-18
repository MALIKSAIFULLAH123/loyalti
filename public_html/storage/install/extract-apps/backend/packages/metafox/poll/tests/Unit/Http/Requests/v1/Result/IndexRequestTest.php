<?php

namespace MetaFox\Poll\Tests\Unit\Http\Requests\v1\Result;

use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Http\Requests\v1\Result\IndexRequest as Request;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
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
            $this->shouldRequire('poll_id'),
            $this->failIf('poll_id', 'string', 0, null),
            $this->failIf('answer_id', 'string', 0, null),
            $this->withSampleParameters('page', 'limit')
        );
    }

    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        $form = $this->buildForm(['poll_id' => $poll->entityId()]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }

    public function testAnswerIdExist()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var Answer $answer */
        $answer = Answer::factory()->setPoll($poll)->create();

        $form = $this->buildForm([
            'poll_id'   => $poll->entityId(),
            'answer_id' => $answer->entityId() + 50,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
