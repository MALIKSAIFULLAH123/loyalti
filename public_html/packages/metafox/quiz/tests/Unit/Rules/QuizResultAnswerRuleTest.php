<?php

namespace MetaFox\Quiz\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Rules\QuizResultAnswerRule;
use Tests\TestCase;

class QuizResultAnswerRuleTest extends TestCase
{
    /**
     * @throws ValidationException
     */
    public function testValidateSuccess(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->setUserAndOwner($user)->create();

        $answers  = [];
        foreach ($quiz->questions as $question) {
            $answers[$question->id] = $question->answers->first()->id;
        }

        $data = [
            'answers' => $answers,
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new QuizResultAnswerRule($quiz->entityId()),
            ],
        ]);
        $this->assertIsArray($validator->validate());
    }

    /**
     * @throws ValidationException
     */
    public function testValidateFailWithNonExistQuestionId(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        /** @var Quiz $quiz */
        $quiz      = Quiz::factory()->setUserAndOwner($user)->create();
        $otherQuiz = Quiz::factory()->setUserAndOwner($user)->create();

        /**
         * @var Question $question
         * @var Question $invalidQuestion
         */
        $question        = Question::factory()->setQuiz($quiz)->create();
        $invalidQuestion = Question::factory()->setQuiz($otherQuiz)->create();

        /** @var Answer $answer */
        $answer = Answer::factory()->setQuestion($question)->create();

        $data = [
            'answers' => [
                $invalidQuestion->entityId() => $answer->entityId(),
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new QuizResultAnswerRule($quiz->entityId()),
            ],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }

    /**
     * @throws ValidationException
     */
    public function testValidateFailWithNonExistAnswerId(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        /** @var Quiz $quiz */
        $quiz      = Quiz::factory()->setUserAndOwner($user)->create();
        $otherQuiz = Quiz::factory()->setUserAndOwner($user)->create();

        /**
         * @var Question $question
         * @var Question $invalidQuestion
         */
        $question        = Question::factory()->setQuiz($quiz)->create();
        $invalidQuestion = Question::factory()->setQuiz($otherQuiz)->create();

        /** @var Answer $invalidAnswer */
        $invalidAnswer = Answer::factory()->setQuestion($invalidQuestion)->create();

        $data = [
            'answers' => [
                $question->entityId() => $invalidAnswer->entityId(),
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new QuizResultAnswerRule($quiz->entityId()),
            ],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }

    /**
     * @throws ValidationException
     */
    public function testValidateFailWhenAllQuestionsAreNotAnswered(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->setUserAndOwner($user)->create();

        /**
         * @var Question $question1
         * @var Question $question2
         */
        $question1 = Question::factory()->setQuiz($quiz)->create();
        $question2 = Question::factory()->setQuiz($quiz)->create();

        /** @var Answer $answer1 */
        $answer1 = Answer::factory()->setQuestion($question1)->create();
        Answer::factory()->setQuestion($question1)->create();

        Answer::factory()->setQuestion($question2)->create();
        Answer::factory()->setQuestion($question2)->create();

        $data = [
            'answers' => [
                $question1->entityId() => $answer1->entityId(),
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new QuizResultAnswerRule($quiz->entityId()),
            ],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }
}
