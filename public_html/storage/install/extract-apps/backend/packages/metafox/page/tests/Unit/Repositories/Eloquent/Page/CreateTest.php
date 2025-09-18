<?php

namespace MetaFox\Page\Tests\Unit\Repositories\Eloquent\Page;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Repositories\Eloquent\PageRepository;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\UserRole;
use Prettus\Validator\Exceptions\ValidatorException;
use Tests\TestCase;
use Twilio\Jwt\TaskRouter\Policy;

class CreateTest extends TestCase
{
    protected PageRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = resolve(PageRepositoryInterface::class);
    }

    public function testInstance()
    {
        $this->assertInstanceOf(PageRepository::class, $this->repository);
    }

    /**
     * @throws ValidatorException|AuthorizationException
     */
    public function testSuccess()
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $category = Category::factory()->create();

        $params = [
            'name'        => $this->faker->name,
            'category_id' => $category->entityId(),
        ];

        $this->actingAs($user);
        $this->skipPolicies(Policy::class);

        $page = $this->repository->createPage($user, $params);
        $page->refresh();
        $this->assertNotEmpty($page);
        $this->assertTrue($page->isAdmin($user));
    }
}
