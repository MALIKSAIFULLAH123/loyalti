<?php

namespace MetaFox\Quiz\Tests\Unit\Models;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use Tests\TestCase;

class AnswerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testAnswer()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $quiz = Quiz::factory()->setUser($user)->setOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $question = Question::factory()->setQuiz($quiz)->create();

        /** @var Answer $answer */
        $answer = Answer::factory()->setQuestion($question)->create(['is_correct' => 1]);
        $answer->refresh();

        $this->assertNotEmpty($answer->entityId());
        $this->assertNotEmpty($answer->question->entityId());
        $this->assertNotEmpty($answer->question->quiz->entityId());
        $this->assertNotEmpty($answer->question->quiz->owner->entityId());
        $this->assertNotEmpty($answer->question->quiz->owner->entityType());
        $this->assertNotEmpty($answer->question->quiz->user->entityId());
        $this->assertNotEmpty($answer->question->quiz->user->entityType());
    }
}

// end
