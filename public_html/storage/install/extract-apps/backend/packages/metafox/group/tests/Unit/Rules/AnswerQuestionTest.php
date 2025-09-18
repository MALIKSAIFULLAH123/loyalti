<?php

namespace MetaFox\Group\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Rules\AnswerQuestion;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class AnswerQuestionTest extends TestCase
{
    public function testInstance()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $group = Group::factory()
            ->setUser($user)
            ->setPrivacyType(MetaFoxPrivacy::EVERYONE)
            ->create([
                'is_answer_membership_question' => true,
            ]);

        $this->assertInstanceOf(Group::class, $group);

        $question = Question::factory()
            ->create([
                'group_id' => $group->entityId(),
                'question' => $this->faker->title,
                'type_id'  => Question::TYPE_TEXT,
            ]);

        $this->assertInstanceOf(Question::class, $question);

        return [$group, $question];
    }

    /**
     * @depends testInstance
     */
    public function testValidateSuccess(array $data)
    {
        [$group, $question] = $data;

        $data = [
            'question' => [
                'question_' . $question->entityId() => $this->faker->text,
            ],
        ];

        $validator = Validator::make($data, [
            'question' => [new AnswerQuestion($group)],
        ]);

        $this->assertIsArray($validator->validate());
    }

    /**
     * @depends testInstance
     */
    public function testValidateFail(array $data)
    {
        [$group, $question] = $data;

        $data = [
            'question' => [
                'question_' . $question->entityId() => '',
            ],
        ];

        $validator = Validator::make($data, [
            'question' => [new AnswerQuestion($group)],
        ]);

        $this->expectException(ValidationException::class);

        $validator->validate();
    }
}
