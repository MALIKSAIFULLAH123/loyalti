<?php

namespace MetaFox\Quiz\Tests\Unit\Http\Resources\v1\Quiz;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Http\Resources\v1\Quiz\EditQuizForm as Form;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz as Model;
use Tests\TestCase;

/**
 * --------------------------------------------------------------------------.
 * @link \MetaFox\Quiz\Http\Resources\v1\Quiz\EditForm::initialize
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form_test.stub
 */

/**
 * Class EditQuizFormTest.
 */
class EditQuizFormTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    public function testGenerateItem(): array
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $quiz = Model::factory()->create([
            'user_id'  => $user->entityId(),
            'owner_id' => $user->entityId(),
            'privacy'  => MetaFoxPrivacy::EVERYONE,
        ]);
        $this->assertInstanceOf(Model::class, $quiz);
        $this->assertNotEmpty($quiz);

        $question = Question::factory()->setQuiz($quiz)->create();
        $this->assertInstanceOf(Question::class, $question);

        Answer::factory()->setQuestion($question)->create(['is_correct' => 1]);
        Answer::factory()->setQuestion($question)->create();
        Answer::factory()->setQuestion($question)->create();
        Answer::factory()->setQuestion($question)->create();

        return [$user, $quiz];
    }

    /**
     * @depends testGenerateItem
     * @param array<int, mixed> $params
     */
    public function testEditQuizForm(array $params)
    {
        [$user, $item] = $params;
        $this->be($user);

        $form = new Form($item);
        $data = $form->toArray(null);
        $this->assertIsArray($data);
    }
}
