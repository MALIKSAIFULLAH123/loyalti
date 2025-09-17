<?php

namespace MetaFox\Poll\Tests\Unit\Http\Requests\v1\Result;

use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Http\Requests\v1\Result\StoreRequest as Request;
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
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('poll_id'),
            $this->failIf('poll_id', 0, null, 'string', []),
            $this->failIf('poll_id', 0, null, 'string', ['string']),
        );
    }

    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var Answer $answer */
        $answer = Answer::factory()->setPoll($poll)->create();

        $form = $this->buildForm([
            'poll_id' => $poll->entityId(),
            'answers' => [$answer->entityId()],
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }

    public function testPollIdExist()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var Answer $answer */
        $answer = Answer::factory()->setPoll($poll)->create();

        $form = $this->buildForm([
            'poll_id' => $poll->entityId() + 50,
            'answers' => [$answer->entityId()],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testPollNotClosed()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create([
            'privacy'   => MetaFoxPrivacy::EVERYONE,
            'closed_at' => (int) (now()->getPreciseTimestamp(0) - (3600 * 24)),
        ]);

        /** @var Answer $answer */
        $answer = Answer::factory()->setPoll($poll)->create();

        $form = $this->buildForm([
            'poll_id' => $poll->entityId(),
            'answers' => [$answer->entityId()],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersItemMin()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        Answer::factory()->setPoll($poll)->create();

        $form = $this->buildForm([
            'poll_id' => $poll->entityId(),
            'answers' => [0],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersItemExist()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        Answer::factory()->setPoll($poll)->create();

        $form = $this->buildForm([
            'poll_id' => $poll->entityId(),
            'answers' => [0],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testAnswersCorrectLengthWhenNotMultiple()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var Poll $poll */
        $poll = Poll::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var Answer $answer1 */
        $answer1 = Answer::factory()->setPoll($poll)->create();

        /** @var Answer $answer2 */
        $answer2 = Answer::factory()->setPoll($poll)->create();

        $form = $this->buildForm([
            'poll_id' => $poll->entityId(),
            'answers' => [$answer1->entityId(), $answer2->entityId()],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
