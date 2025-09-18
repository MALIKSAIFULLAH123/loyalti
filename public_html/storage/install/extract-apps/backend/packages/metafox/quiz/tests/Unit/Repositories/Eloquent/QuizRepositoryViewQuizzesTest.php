<?php

namespace MetaFox\Quiz\Tests\Unit\Repositories\Eloquent;

use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Helper\Pagination;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz as Model;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\ViewScope;
use Tests\TestCase;

class QuizRepositoryViewQuizzesTest extends TestCase
{
    protected QuizRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(QuizRepositoryInterface::class);
    }

    public function testInstance()
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);

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
    public function testViewMyQuizzesWithSponsoredQuizzes(array $params)
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $owner = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        Model::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_SPONSOR,
        ]);

        Model::factory()->setOwner($user)->setUser($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_UN_SPONSOR,
        ]);

        $mySponsoredItem = Model::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_SPONSOR,
        ]);

        $myNormalItem = Model::factory()->setOwner($owner)->setUser($owner)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_approved' => Model::IS_APPROVED,
            'is_sponsor'  => Model::IS_UN_SPONSOR,
        ]);

        $params = array_merge($params, [
            'view' => Browse::VIEW_MY,
        ]);

        $results = $this->repository->viewQuizzes($owner, $owner, $params)->collect();

        $this->assertNotEmpty($results);
        $this->assertTrue($results->contains('id', $mySponsoredItem->entityId()));
        $this->assertTrue($results->contains('id', $myNormalItem->entityId()));
        $this->assertCount(2, $results); // should not contain another's items
    }
}
