<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Quiz;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Friend\Database\Factories\FriendListDataFactory;
use MetaFox\Friend\Models\FriendList;
use MetaFox\Group\Database\Factories\GroupFactory;
use MetaFox\Group\Database\Factories\MemberFactory;
use MetaFox\Group\Support\PrivacyTypeHandler;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use Tests\TestCase;

class QuizRepositoryActionCreateQuizTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testCreateQuiz()
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
        $item       = $repository->createQuiz($user, $user, $params);
        $this->assertNotEmpty($item->entityId());

        $quizText = $item->quizText;
        $this->assertNotEmpty($quizText->text);
        $this->assertNotEmpty($quizText->text_parsed);

        $quizQuestions = $item->questions;
        $this->assertTrue($quizQuestions->isNotEmpty());

        $quizQuestionAnswers = $quizQuestions->first()->answers;
        $this->assertTrue($quizQuestionAnswers->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testCreateQuizWithOwner()
    {
        $groupOwner  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $publicGroup = GroupFactory::new()->setUser($groupOwner)
            ->setPrivacyType(PrivacyTypeHandler::PUBLIC)
            ->create();

        MemberFactory::new()->setUser($groupOwner)->setOwner($publicGroup)->setAdmin()->create();

        $params = [
            'title'     => $this->faker->sentence . rand(1, 999),
            'text'      => $this->faker->text,
            'questions' => [
                [
                    'question' => $this->faker->sentence . rand(1, 999),
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
                    'ordering' => 1,
                ],
            ],
            'privacy' => 0,
        ];

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);
        $item       = $repository->createQuiz($groupOwner, $publicGroup, $params);
        $this->assertNotEmpty($item->entityId());
        $this->assertTrue($item->ownerId() == $publicGroup->entityId());
    }

    /**
     * @throws AuthorizationException
     */
    public function testCreateQuizWithPrivacyCustom()
    {
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        FriendFactory::new()->setUser($user1)->setOwner($user2)->create();
        FriendFactory::new()->setUser($user2)->setOwner($user1)->create();

        $list1 = FriendList::factory()->setUser($user1)->create();
        $list2 = FriendList::factory()->setUser($user1)->create();

        FriendListDataFactory::new(['list_id' => $list1->id])->setUser($user2)->create();
        FriendListDataFactory::new(['list_id' => $list2->id])->setUser($user2)->create();

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);
        $params     = [
            'title'     => $this->faker->sentence . rand(1, 999),
            'text'      => $this->faker->text,
            'questions' => [
                [
                    'question' => $this->faker->sentence . rand(1, 999),
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
                    'ordering' => 1,
                ],
            ],
            'privacy' => MetaFoxPrivacy::CUSTOM,
            'list'    => [$list1->id, $list2->id],
        ];

        $item = $repository->createQuiz($user1, $user1, $params);

        $this->assertNotEmpty($item->entityId());
    }
}

// end
