<?php

namespace MetaFox\Poll\Tests\Unit\Repositories\Eloquent\Poll;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Contracts\HasAlphabetSort;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Repositories\Eloquent\PollRepository;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Support\Browse\Scopes\Poll\SortScope;
use MetaFox\Poll\Support\Browse\Scopes\Poll\ViewScope;
use Tests\TestCase;

class PollRepositoryViewPollsTest extends TestCase
{
    protected PollRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PollRepositoryInterface::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function testInstance(): array
    {
        $repository = resolve(PollRepositoryInterface::class);
        $this->assertInstanceOf(PollRepository::class, $repository);

        Poll::factory()->count(2)->create(['privacy' => 0]);

        $items = Poll::query()->paginate(Pagination::DEFAULT_ITEM_PER_PAGE);
        $this->assertTrue($items->isNotEmpty());

        return [
            'q'         => '',
            'sort'      => SortScope::SORT_DEFAULT,
            'sort_type' => SortScope::SORT_TYPE_DEFAULT,
            'when'      => WhenScope::WHEN_DEFAULT,
            'view'      => ViewScope::VIEW_DEFAULT,
            'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
            'user_id'   => 0,
        ];
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewAllPoll(array $params)
    {
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $repository = resolve(PollRepositoryInterface::class);
        $items      = $repository->viewPolls($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewPollsWithOwnerId(array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Poll::factory()->setUser($user2)->setOwner($user2)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $repository        = resolve(PollRepositoryInterface::class);
        $params['user_id'] = $user2->entityId();
        $items             = $repository->viewPolls($user, $user2, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewPollsOnMyProfile(array $params)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Poll::factory()->setUser($user2)->setOwner($user2)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $repository        = resolve(PollRepositoryInterface::class);
        $params['user_id'] = $user2->entityId();
        $items             = $repository->viewPolls($user2, $user2, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    public static function prepareDataForViewPollsWithSortAndWhen(): array
    {
        return [
            [['sort' => Browse::SORT_MOST_VIEWED]],
            [['sort' => Browse::SORT_MOST_DISCUSSED]],
            [['sort' => Browse::SORT_MOST_LIKED]],
            [['sort' => SortScope::SORT_MOST_VOTED]],
            [['sort' => HasAlphabetSort::SORT_ALPHABETICAL]],
            [['when' => Browse::WHEN_TODAY]],
            [['when' => Browse::WHEN_THIS_MONTH]],
            [['when' => Browse::WHEN_THIS_WEEK]],
        ];
    }

    /**
     * @depends      testInstance
     * @dataProvider prepareDataForViewPollsWithSortAndWhen
     *
     * @param array<string, mixed> $params
     *
     * @throws AuthorizationException
     */
    public function testViewPollsWithSortAndWhen(array $data, array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $params = array_merge($params, $data);

        $repository = resolve(PollRepositoryInterface::class);
        $items      = $repository->viewPolls($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    public static function prepareDataForViewPollsWithView(): array
    {
        return [
            [['view' => Browse::VIEW_MY]],
            [['view' => Browse::VIEW_FRIEND]],
            [['view' => Browse::VIEW_PENDING]],
        ];
    }

    /**
     * @depends      testInstance
     * @dataProvider prepareDataForViewPollsWithView
     *
     * @param array<string, mixed> $params
     *
     * @throws AuthorizationException
     */
    public function testViewPollsWithView(array $data, array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::STAFF_USER);
        $user1 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $params = array_merge($params, $data);

        Poll::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);
        Poll::factory()->setUser($user1)->setOwner($user1)->create(['privacy' => 0]);

        FriendFactory::new()->setUser($user)->setOwner($user1)->create();
        FriendFactory::new()->setUser($user1)->setOwner($user)->create();

        $repository = resolve(PollRepositoryInterface::class);
        $items      = $repository->viewPolls($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewPollsWithSearchQuery(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $question = $this->faker->sentence . rand(1, 999);
        Poll::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0, 'question' => $question]);

        $params['q'] = $question;
        $repository  = resolve(PollRepositoryInterface::class);
        $items       = $repository->viewPolls($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewPollsEmpty(array $params): void
    {
        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2      = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $repository = resolve(PollRepositoryInterface::class);
        $items      = $repository->viewPolls($user, $user2, $params);

        $this->assertTrue($items->isEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllPollWithViewFeature(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $question = $this->faker->sentence . rand(1, 999);
        Poll::factory()->setUser($user)->setOwner($user)->create([
            'privacy'     => 0,
            'question'    => $question,
            'is_featured' => HasFeature::IS_FEATURED,
        ]);

        /** @var PollRepository $repository */
        $repository = resolve(PollRepositoryInterface::class);

        $params['view'] = Browse::VIEW_FEATURE;
        $items          = $repository->viewPolls($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends      testInstance
     * @throws AuthorizationException
     */
    public function testViewAllPollWithViewSponsor(array $params): void
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $question = $this->faker->sentence . rand(1, 999);
        Poll::factory()->setUser($user)->setOwner($user)->create([
            'privacy'    => 0,
            'question'   => $question,
            'is_sponsor' => HasSponsor::IS_SPONSOR,
        ]);

        /** @var PollRepository $repository */
        $repository = resolve(PollRepositoryInterface::class);

        $params['view'] = Browse::VIEW_SPONSOR;
        $items          = $repository->viewPolls($user, $user, $params);

        $this->assertTrue($items->isNotEmpty());
    }

    /**
     * @depends testInstance
     * @throws AuthorizationException
     */
    public function testViewMyPollsWithSponsoredPolls(array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Poll::IS_APPROVED,
            'is_sponsor'  => Poll::IS_SPONSOR,
        ]);

        Poll::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Poll::IS_APPROVED,
            'is_sponsor'  => Poll::IS_UN_SPONSOR,
        ]);

        $mySponsoredItem = Poll::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Poll::IS_APPROVED,
            'is_sponsor'  => Poll::IS_SPONSOR,
        ]);

        $myNormalItem = Poll::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Poll::IS_APPROVED,
            'is_sponsor'  => Poll::IS_UN_SPONSOR,
        ]);

        $params = array_merge($params, [
            'view' => Browse::VIEW_MY,
        ]);

        $results = $this->repository->viewPolls($owner, $owner, $params)->collect();

        $this->assertNotEmpty($results);
        $this->assertTrue($results->contains('id', $mySponsoredItem->entityId()));
        $this->assertTrue($results->contains('id', $myNormalItem->entityId()));
        $this->assertCount(2, $results); // should not contain another's items
    }
}

// end
