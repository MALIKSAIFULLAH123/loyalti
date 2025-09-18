<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Quiz;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Answer;
use MetaFox\Quiz\Models\Question;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use Tests\TestCase;

class QuizRepositoryActionUpdateQuizTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $item = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);
        $this->assertNotEmpty($item);

        return $item;
    }

    /**
     * @depends testInstance
     * @param Model $item
     */
    public function testUpdateQuizWithoutPermission(Model $item)
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $params = [
            'title'     => $this->faker->sentence . rand(1, 999),
            'text'      => $this->faker->text,
            'questions' => [
                [
                    'question' => $this->faker->sentence . rand(1, 999),
                    'ordering' => 1,
                    'answers'  => [
                        [
                            'answer'     => $this->faker->sentence . rand(1, 999),
                            'is_correct' => 1,
                            'ordering'   => 1,
                        ],
                        [
                            'answer'     => $this->faker->sentence . rand(1, 999),
                            'is_correct' => 0,
                            'ordering'   => 2,
                        ],
                        [
                            'answer'     => $this->faker->sentence . rand(1, 999),
                            'is_correct' => 0,
                            'ordering'   => 3,
                        ],
                        [
                            'answer'     => $this->faker->sentence . rand(1, 999),
                            'is_correct' => 0,
                            'ordering'   => 4,
                        ],
                    ],
                ],
            ],
            'privacy' => 0,
        ];

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);
        $this->expectException(AuthorizationException::class);
        $repository->updateQuiz($user, $item->entityId(), $params);
    }

    /**
     * @throws AuthorizationException
     */
    public function testUpdateQuiz()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        /** @var Model $item */
        $item = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var Question $question */
        $question = Question::factory()->setQuiz($item)->create();

        /** @var Collection $answers */
        $answers = Answer::factory()->setQuestion($question)->count(4)->create();

        $params = [
            'title'     => 'Quiz Updated',
            'text'      => $this->faker->text . 'updated',
            'questions' => [
                'changedQuestions' => [
                    $question->entityId() => [
                        'id'       => $question->entityId(),
                        'question' => 'Question Updated',
                        'ordering' => 1,
                        'answers'  => [
                            'newAnswers' => [
                                [
                                    'answer'     => $this->faker->sentence . rand(1, 999),
                                    'is_correct' => 1,
                                    'ordering'   => 1,
                                ],
                                [
                                    'answer'     => $this->faker->sentence . rand(1, 999),
                                    'is_correct' => 0,
                                    'ordering'   => 2,
                                ],
                            ],
                        ],
                    ],
                ],
                'newQuestions' => [
                    [
                        'question' => 'New Question',
                        'ordering' => 2,
                        'answers'  => [
                            'newAnswers' => [
                                [
                                    'answer'     => $this->faker->sentence . rand(1, 999),
                                    'is_correct' => 1,
                                    'ordering'   => 1,
                                ],
                                [
                                    'answer'     => $this->faker->sentence . rand(1, 999),
                                    'is_correct' => 0,
                                    'ordering'   => 2,
                                ],
                                [
                                    'answer'     => $this->faker->sentence . rand(1, 999),
                                    'is_correct' => 0,
                                    'ordering'   => 3,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'privacy' => 0,
        ];

        /** @var QuizRepository $repository */
        $repository  = resolve(QuizRepositoryInterface::class);
        $updatedItem = $repository->updateQuiz($user, $item->entityId(), $params);

        $this->assertTrue($updatedItem->entityId() == $item->entityId());

        $quizQuestions = $updatedItem->questions()->get();
        $this->assertTrue($quizQuestions->isNotEmpty());
        $this->assertTrue(Question::query()->find($question->entityId())->get()->isNotEmpty());

        /** @var Collection $quizQuestionAnswers */
        $quizQuestionAnswers = $quizQuestions->first()->answers;
        $this->assertTrue($quizQuestionAnswers->isNotEmpty());
        $this->assertTrue(Answer::query()->whereIn('id', $answers->pluck('id'))->get()->isEmpty());
    }

    /**
     * @throws AuthorizationException
     */
    public function testUpdateQuizWithPrivacyCustom()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user)->setOwner($user1)->create();
        FriendFactory::new()->setUser($user1)->setOwner($user)->create();

        $list1 = FriendList::factory()->setUser($user)->create();
        $list2 = FriendList::factory()->setUser($user)->create();

        FriendListDataFactory::new(['list_id' => $list1->id])->setUser($user1)->create();
        FriendListDataFactory::new(['list_id' => $list2->id])->setUser($user1)->create();

        /** @var Model $quiz */
        $quiz = Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);
        $params     = [
            'privacy' => MetaFoxPrivacy::CUSTOM,
            'list'    => [$list1->id, $list2->id],
        ];

        $updatedItem = $repository->updateQuiz($user, $quiz->entityId(), $params);

        $this->assertNotEmpty($updatedItem->entityId());
        $this->assertNotEmpty(MetaFoxPrivacy::CUSTOM == $updatedItem->privacy);
    }
}

// end
