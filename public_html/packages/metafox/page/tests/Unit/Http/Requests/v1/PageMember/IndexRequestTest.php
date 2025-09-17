<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\PageMember;

use MetaFox\Page\Http\Requests\v1\PageMember\IndexRequest as Request;
use MetaFox\Page\Models\Category;
use MetaFox\Page\Models\Page;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class IndexRequestTest.
 */
class IndexRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('page_id'),
            $this->failIf('excluded_user_id', 0, null, []),
            $this->withSampleParameters('q', 'page', 'limit', 'view')
        );
    }

    public function testSuccess()
    {
        $category = Category::factory()->create();
        $user     = $this->createNormalUser();
        $this->be($user);

        $page = Page::factory()->setUser($user)
            ->create([
                'category_id' => $category->entityId(),
            ]);
        $form = $this->buildForm([
            'page_id' => $page->entityId(),

        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }
}
