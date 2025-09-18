<?php

namespace MetaFox\Quiz\Tests\Unit\Support\Browse\Scope\Quiz;

use MetaFox\Friend\Database\Factories\FriendFactory;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
use MetaFox\Quiz\Repositories\Eloquent\QuizRepository;
use MetaFox\Quiz\Repositories\QuizRepositoryInterface;
use MetaFox\Quiz\Support\Browse\Scopes\Quiz\ViewScope;
use Tests\TestCase;

class ViewScopeTest extends TestCase
{
    public function testInstance()
    {
        $repository = resolve(QuizRepositoryInterface::class);
        $this->assertInstanceOf(QuizRepository::class, $repository);
    }

    /**
     * @depends testInstance
     */
    public function testViewDefault()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $this->be($user);

        $items = Quiz::factory()->count(2)->setUserAndOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_ALL)->setUserContext($user)->setIsViewOwner(true);

        $repository = resolve(QuizRepositoryInterface::class);
        $result     = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(2);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testWithViewMy()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $items = Quiz::factory()->count(2)->setUserAndOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_MY)->setUserContext($user)->setIsViewOwner(true);

        $repository = resolve(QuizRepositoryInterface::class);
        $result     = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(2);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testWithViewPending()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $items = Quiz::factory()->count(2)->setUserAndOwner($user)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
            'is_approved' => 0,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_PENDING)->setUserContext($user)->setIsViewOwner(true);

        $repository = resolve(QuizRepositoryInterface::class);
        $result     = $repository->getModel()->newQuery()
            ->addScope($viewScope)->simplePaginate(2);

        $this->assertNotEmpty($result->items());
    }

    /**
     * @depends testInstance
     */
    public function testWithViewOnProfile()
    {
        $user  = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $items = Quiz::factory()->count(2)->setUserAndOwner($user2)->create([
            'privacy'     => MetaFoxPrivacy::EVERYONE,
            'is_featured' => 0,
            'is_sponsor'  => 0,
        ]);

        $this->assertNotEmpty($items->toArray());

        $viewScope = new ViewScope();
        $viewScope->setView(Browse::VIEW_FRIEND)->setUserContext($user)->setIsViewOwner(false)->setIsViewOwner(true);

        $repository = resolve(QuizRepositoryInterface::class);
        $result     = $repository->getModel()->newQuery()
            ->addScope($viewScope)->where('quizzes.user_id', $user2->entityId())->simplePaginate(20);

        $this->assertNotEmpty($result->items());
        $checkCount = 2;
        $this->assertTrue($checkCount == count($result->items()));
    }
}
