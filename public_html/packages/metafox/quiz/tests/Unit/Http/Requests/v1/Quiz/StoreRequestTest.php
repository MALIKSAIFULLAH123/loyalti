<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Requests\v1\Quiz;

use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
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
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return \MetaFox\Quiz\Http\Requests\v1\Quiz\StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('title', 'text', 'questions', 'file', 'privacy'),
            $this->failIf('title', 0, [], 'A', str_pad('A', 500, 'A')),
            $this->failIf('file', null, 0, 'string'),
            $this->failIf('text', null, 0, []),
            $this->failIf('questions', 0, null, [], ['Test'])
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $user = $this->createNormalUser();
        $this->be($user);
    }

    public function testSuccess()
    {
        $this->markTestIncomplete('Mock quiz require photo');

        $form = $this->buildForm([
            'title'     => $this->faker->title,
            'text'      => $this->faker->text,
            'questions' => [
                [
                    'question' => $this->faker->words(5, true),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                            'ordering'   => 1,
                        ],
                        [
                            'answer'   => $this->faker->words(5, true),
                            'ordering' => 2,
                        ],
                    ],
                    'ordering' => 1,
                ],
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }

    public function testQuestionsQuestionRequired()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'answers' => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsQuestionString()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => 1,
                    'answers'  => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsQuestionMin()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => 'ss',
                    'answers'  => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsQuestionMax()
    {
        $this->beforeTest();
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersMin()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersHaveCorrectAnswer()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 0,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersHaveItemIsArray()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        'test1', 'test2', 'test3', 'test4',
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersAnswerRequired()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersAnswerString()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => 1,
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersAnswerMin()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => '',
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersAnswerMax()
    {
        $this->beforeTest();
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence(256),
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersAnswerIsCorrectNumeric()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence(256),
                            'is_correct' => 's',
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersAnswerIsCorrectInAllow()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence(256),
                            'is_correct' => 2,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileArray()
    {
        $this->beforeTest();
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence,
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'file'    => 's',
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileRequiredWithFile()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence,
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text' => $this->faker->text,
            'file' => [
                'test' => 'test',
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileNumeric()
    {
        $this->beforeTest();

        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence,
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text' => $this->faker->text,
            'file' => [
                'temp_file' => 's',
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileExist()
    {
        $this->beforeTest();
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence,
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                ],
            ],
            'text' => $this->faker->text,
            'file' => [
                'temp_file' => 0,
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
