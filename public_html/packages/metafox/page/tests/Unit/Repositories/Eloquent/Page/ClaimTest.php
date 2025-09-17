<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\Page;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\Eloquent\PageClaimRepository;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    /**
     * @return PageClaimRepositoryInterface
     */
    public function testInstance()
    {
        $repository = resolve(PageClaimRepositoryInterface::class);
        $this->assertInstanceOf(PageClaimRepository::class, $repository);

        return $repository;
    }

    /**
     * @depends testInstance
     *
     * @param PageClaimRepositoryInterface $repository
     *
     * @throws AuthorizationException
     */
    public function testSuccess(PageClaimRepositoryInterface $repository)
    {
        $adminPage = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $this->actingAs($adminPage);

        $this->skipPolicies(PagePolicy::class);

        $page = Page::factory()->setUser($adminPage)->setOwner($adminPage)->create();

        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $result = $repository->createPageClaim($user, $page->entityId(), $this->faker->text);
        $this->assertTrue($result);
    }
}
