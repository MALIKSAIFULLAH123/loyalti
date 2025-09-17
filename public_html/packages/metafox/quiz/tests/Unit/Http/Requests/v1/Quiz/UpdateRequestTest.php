<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Requests\v1\Quiz;

use Illuminate\Validation\ValidationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Quiz\Http\Requests\v1\Quiz\UpdateRequest as Request;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
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
            $this->failIf('title', 0, [], 'A', str_pad('A', 500, 'A')),
            $this->failIf('file', 0, 'string'),
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

        $quiz = Quiz::factory()->setUser($user)->setOwner($user)->create([
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);

        /**
         * @var Quiz     $quiz
         * @var Question $question
         * @var Answer   $answer
         */
        $question = Question::factory()->setQuiz($quiz)->create();

        $answer = Answer::factory()->setQuestion($question)->create();

        $form = $this->buildForm([
            'title'     => $this->faker->title,
            'text'      => $this->faker->text,
            'questions' => [
                [
                    'id'       => $question->entityId(),
                    'question' => $this->faker->words(5, true),
                    'answers'  => [
                        [
                            'id'         => $answer->entityId(),
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
                        ],
                    ],
                    'ordering' => 1,
                ],
                [
                    'question' => $this->faker->words(5, true),
                    'answers'  => [
                        [
                            'answer'     => $this->faker->words(5, true),
                            'is_correct' => 1,
                        ],
                        [
                            'answer' => $this->faker->words(5, true),
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

    public function testQuestionsAnswersRequired()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence,
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testQuestionsAnswersArray()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => $this->faker->word,
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

    public function testQuestionsAnswersIdNumeric()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'id'         => 's',
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

    public function testQuestionsAnswersIdExist()
    {
        $form = $this->buildForm([
            'title'     => $this->faker->sentence,
            'questions' => [
                [
                    'question' => $this->faker->sentence(256),
                    'answers'  => [
                        [
                            'id'         => 0,
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

    public function testQuestionsAnswersAnswerRequired()
    {
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

    public function testItemIdExist()
    {
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
            'item_id' => 0,
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testItemIdNumeric()
    {
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
            'item_id' => 's',
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileArray()
    {
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

    public function testFileStatusRequiredWithFile()
    {
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
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                    ],
                ],
            ],
            'text' => $this->faker->text,
            'file' => [
                'status' => 'remove',
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileStatusInAllow()
    {
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
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                    ],
                ],
            ],
            'text' => $this->faker->text,
            'file' => [
                'status' => 'test',
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileRequired()
    {
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
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                    ],
                ],
            ],
            'text' => $this->faker->text,
            'file' => [
                'status' => 'update',
            ],
            'privacy' => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testFileTempFileNumeric()
    {
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
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
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
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
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

    public function testListRequiredIf()
    {
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
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                        [
                            'answer' => $this->faker->sentence . rand(1, 999),
                        ],
                    ],
                ],
            ],
            'text'    => $this->faker->text,
            'privacy' => MetaFoxPrivacy::CUSTOM,
        ]);
        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
