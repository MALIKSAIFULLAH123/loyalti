<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Requests\v1\Result;

use MetaFox\Quiz\Http\Requests\v1\Result\IndexRequest;
use MetaFox\Quiz\Models\Quiz;
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
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return IndexRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('quiz_id'),
            $this->failIf('quiz_id', 0, null, 'string'),
            $this->withSampleParameters('limit', 'page')
        );
    }

    public function testSuccess()
    {
        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->create();
        $form = $this->buildForm([
            'quiz_id' => $quiz->entityId(),
            'limit'   => 10,
        ]);
        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
