<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageMember;

use MetaFox\Page\Http\Requests\v1\PageMember\DeletePageAdminRequest as Request;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class DeletePageAdminRequestTest.
 */
class DeletePageAdminRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('page_id', 'user_id', 'is_delete'),
            $this->failIf('page_id', 0, null, 'string'),
            $this->failIf('is_delete', 'string', null, 99, []),
            $this->failIf('user_id', 0, 'string', []),
        );
    }

    public function testCreatePage(): Page
    {
        $category = Category::factory()->create();
        $user     = $this->createNormalUser();
        $this->be($user);

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);

        $this->assertNotEmpty($page);

        return $page;
    }

    /**
     * @depends testCreatePage
     */
    public function testSuccess(Page $page)
    {
        $user2 = $this->createUser()->assignRole(UserRole::NORMAL_USER);

        $form = $this->buildForm([
            'page_id'   => $page->entityId(),
            'user_id'   => $user2->entityId(),
            'is_delete' => 1,
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }
}
