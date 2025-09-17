<?php

namespace MetaFox\Quiz\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Result;
use MetaFox\Quiz\Rules\QuizResultQuizIdRule;
use Tests\TestCase;

class QuizResultQuizIdRuleTest extends TestCase
{
    /**
     * @throws ValidationException
     */
    public function testValidateSuccess(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->setUserAndOwner($user)->create();

        $data = [
            'quiz_id' => $quiz->entityId(),
        ];
        $validator = Validator::make($data, [
            'quiz_id' => [
                new QuizResultQuizIdRule($user),
            ],
        ]);
        $this->assertIsArray($validator->validate());
    }

    /**
     * @throws ValidationException
     */
    public function testValidateFailWithNonExistQuizId(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $data = [
            'quiz_id' => 0,
        ];
        $validator = Validator::make($data, [
            'quiz_id' => [new QuizResultQuizIdRule($user)],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }

    /**
     * @throws ValidationException
     */
    public function testValidateFailWithQuizAlreadyAnswered(): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        /** @var Quiz $quiz */
        $quiz = Quiz::factory()->setUserAndOwner($user)->create();

        Result::factory()->setUser($user)->setQuiz($quiz)->create();

        $data = [
            'quiz_id' => $quiz->entityId(),
        ];

        $validator = Validator::make($data, [
            'quiz_id' => [new QuizResultQuizIdRule($user)],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }
}
