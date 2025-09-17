<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent\Quiz;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\SortScope;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\ViewScope;
use Tests\TestCase;

class QuizRepositoryActionViewQuizzesTest extends TestCase
{
    public function testInstance(): array
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
        $this->assertTrue(true);

        Model::factory()->count(2)->create(['privacy' => 0]);

        $items = Model::query()->paginate(Pagination::DEFAULT_ITEM_PER_PAGE);
        $this->assertTrue($items->isNotEmpty());

        return [
            'q'           => '',
            'sort'        => SortScope::SORT_DEFAULT,
            'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
            'when'        => WhenScope::WHEN_DEFAULT,
            'view'        => ViewScope::VIEW_DEFAULT,
            'limit'       => Pagination::DEFAULT_ITEM_PER_PAGE,
            'category_id' => 0,
            'user_id'     => 0,
        ];
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAllQuiz(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);
        $items      = $repository->viewQuizzes($user, $user, $params);
        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAllQuizWithOwnerId(array $params): void
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user2)->setOwner($user2)->create(['privacy' => 0]);

        /** @var QuizRepository $repository */
        $repository        = resolve(QuizRepositoryInterface::class);
        $params['user_id'] = $user2->entityId();
        $items             = $repository->viewQuizzes($user, $user2, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAllQuizMyProfile(array $params): void
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user2)->setOwner($user2)->create(['privacy' => 0]);

        /** @var QuizRepository $repository */
        $repository        = resolve(QuizRepositoryInterface::class);
        $params['user_id'] = $user2->entityId();
        $items             = $repository->viewQuizzes($user2, $user2, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    public static function prepareDataForViewAllQuizWithSortAndWhen(): array
    {
        return [
            [['sort' => Browse::SORT_MOST_VIEWED]],
            [['sort' => Browse::SORT_MOST_DISCUSSED]],
            [['sort' => Browse::SORT_MOST_LIKED]],
            [['sort' => SortScope::SORT_MOST_PLAYED]],
            [['sort' => SortScope::SORT_ALPHABETICAL]],
            [['when' => Browse::WHEN_TODAY]],
            [['when' => Browse::WHEN_THIS_MONTH]],
            [['when' => Browse::WHEN_THIS_WEEK]],
        ];
    }

    /**
     * @depends      testInstance
     * @dataProvider prepareDataForViewAllQuizWithSortAndWhen
     *
     * @param array<string, mixed> $params
     *
     * @throws AuthorizationException
     */
    public function testViewAllQuizWithSortAndWhen(array $data, array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $params = array_merge($params, $data);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $items = $repository->viewQuizzes($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    public static function prepareDataForViewAllQuizWithView(): array
    {
        return [
            [['view' => Browse::VIEW_MY]],
            [['view' => Browse::VIEW_PENDING]],
            [['view' => Browse::VIEW_FRIEND]],
        ];
    }

    /**
     * @depends      testInstance
     * @dataProvider prepareDataForViewAllQuizWithView
     *
     * @param array<string, mixed> $params
     *
     * @throws AuthorizationException
     */
    public function testViewAllQuizWithView(array $data, array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::ADMIN_USER);
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->actingAs($user);

        $params = array_merge($params, $data);

        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);
        Model::factory()->setUser($user1)->setOwner($user1)->create(['privacy' => 0, 'is_approved' => false]);
        Model::factory()->setUser($user1)->setOwner($user1)->create(['privacy' => 0]);

        FriendFactory::new()->setUser($user)->setOwner($user1)->create();
        FriendFactory::new()->setUser($user1)->setOwner($user)->create();

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);
        $items      = $repository->viewQuizzes($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllQuizWithSearchQuery(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $title = $this->faker->title;
        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'title' => $title]);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $params['q'] = $title;
        $items       = $repository->viewQuizzes($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllQuizEmpty(array $params): void
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $items = $repository->viewQuizzes($user, $user2, $params);

        $this->assertTrue($items->isEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllQuizWithViewFeature(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'is_featured' => Quiz::IS_FEATURED]);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $params['view'] = Browse::VIEW_FEATURE;
        $items          = $repository->viewQuizzes($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllQuizWithViewSponsor(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'is_sponsor' => Quiz::IS_SPONSOR]);

        /** @var QuizRepository $repository */
        $repository = resolve(QuizRepositoryInterface::class);

        $params['view'] = Browse::VIEW_SPONSOR;
        $items          = $repository->viewQuizzes($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }
}

// end
