<?php

namespace MetaFox\Quiz\Tests\Unit\Models;

use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testQuestion()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $quiz = Quiz::factory()->setOwner($user)->setUser($user)->create();

        /** @var Question $question */
        $question = Question::factory()->setQuiz($quiz)->create();
        $question->refresh();

        $this->assertNotEmpty($question->entityId());
        $this->assertNotEmpty($question->quiz->entityId());
        $this->assertNotEmpty($question->quiz->user->entityId());
        $this->assertNotEmpty($question->quiz->user->entityType());
        $this->assertNotEmpty($question->quiz->owner->entityId());
        $this->assertNotEmpty($question->quiz->owner->entityType());
    }
}

// end
