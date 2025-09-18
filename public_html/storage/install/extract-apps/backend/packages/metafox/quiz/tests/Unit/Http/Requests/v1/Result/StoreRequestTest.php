<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Requests\v1\Result;

use Illuminate\Validation\ValidationException;
use MetaFox\Quiz\Http\Requests\v1\Result\StoreRequest;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Quiz\Http\Controllers\Api\ResultController::$controllers;
 * stub: api_action_request_test.stub
 */

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('quiz_id', 'answers'),
            $this->failIf('quiz_id', -1, 0, null, 'string'),
            $this->failIf('answers', -1, 0, null, []),
        );
    }

    /**
     * @testdox Prepare quiz to send requests
     * @test
     * @return Quiz
     */
    public function buildQuizData(): Quiz
    {
        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->create();

        $this->expectNotToPerformAssertions();

        return $quiz;
    }

    /**
     * @depends buildQuizData
     */
    public function testSuccess(Quiz $quiz)
    {
        /** @var Question[] $questions */
        $questions = $quiz->questions;

        $answers =  [];
        foreach ($questions as $question) {
            $answers[$question->entityId()] =  $question->answers->first()?->id;
        }

        $form = $this->buildForm([
            'quiz_id' => $quiz->entityId(),
            'answers' => $answers,
        ]);
        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }

    /**
     * @param  Quiz $quiz
     * @return void
     * @depends buildQuizData
     */
    public function testQuizIdUniqueWithAnUser(Quiz $quiz)
    {
        $this->markTestIncomplete('Should prepare answers');

        $form = $this->buildForm([
            'quiz_id' => $quiz->entityId(),
            'answers' => [],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    /**
     * @param  Quiz $quiz
     * @return void
     * @depends buildQuizData
     */
    public function testAnswersWithAnswerIdExist(Quiz $quiz)
    {
        $this->markTestIncomplete('Should prepare answers');

        $form          = $this->buildForm([
            'quiz_id' => $quiz->entityId(),
            'answers' => [
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    /**
     * @param  Quiz $quiz
     * @return void
     * @depends buildQuizData
     */
    public function testAnswersHavingEnoughAnswer(Quiz $quiz)
    {
        $this->markTestIncomplete('Should prepare answers');

        $form = $this->buildForm([
            'quiz_id' => $quiz->entityId(),
            'answers' => [],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createNormalUser();
        $this->actingAs($this->user);
    }
}
