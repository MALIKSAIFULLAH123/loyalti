<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageMember;

use MetaFox\Page\Http\Requests\v1\PageMember\AddPageAdminRequest as Request;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\UserRole;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class AddPageAdminRequestTest.
 */
class AddPageAdminRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('page_id', 'user_ids'),
            $this->failIf('page_id', 0, null, 'string'),
            $this->failIf('user_ids', 0, null, ['string'], [0, -1]),
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
            'page_id'  => $page->entityId(),
            'user_ids' => [$user2->entityId()],
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }
}
