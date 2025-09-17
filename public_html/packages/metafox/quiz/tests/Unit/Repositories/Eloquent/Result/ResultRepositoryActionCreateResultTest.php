<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Result;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Repositories\Eloquent\ResultRepository;
use MetaFox\Quiz\Repositories\ResultRepositoryInterface;
use Tests\TestCase;

class ResultRepositoryActionCreateResultTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(ResultRepositoryInterface::class);
        $this->assertInstanceOf(ResultRepository::class, $repository);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testCreateResult()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        /** @var Quiz $quiz */
        $quiz            = Quiz::factory()->setUserAndOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $totalPlayBefore = $quiz->total_play;
        /** @var Question $question */
        $question = Question::factory()->setQuiz($quiz)->create();

        /** @var Answer $answer1, $answer2 */
        $answer1 = Answer::factory()->setQuestion($question)->create(['is_correct' => 1]);
        Answer::factory()->setQuestion($question)->create();

        /** @var ResultRepository $repository */
        $repository = resolve(ResultRepositoryInterface::class);
        $item       = $repository->createResult($user, [
            'quiz_id' => $quiz->entityId(),
            'answers' => [
                $question->entityId() => $answer1->entityId(),
            ],
        ]);
        $this->assertInstanceOf(Quiz::class, $item);

        //Check total play is increase
        $this->assertTrue($item->total_play > $quiz->total_play);
    }
}

// end
