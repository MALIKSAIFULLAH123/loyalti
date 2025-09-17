<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Requests\v1\Category\Admin;

use MetaFox\Marketplace\Http\Requests\v1\Category\Admin\DeleteRequest as Request;
use MetaFox\Marketplace\Models\Category as Category;
use Tests\TestFormRequest;

/**
 * Class DeleteRequestTest.
 */
class DeleteRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('migrate_items'),
            $this->passIf(['migrate_items' => 0]),
            $this->failIf('migrate_items', null, []),
            $this->failIf('new_category_id', null, [], 0),
        );
    }

    public function testSuccess()
    {
        $category = Category::factory()->create();

        $form = $this->buildForm([
            'migrate_items'   => 1,
            'new_category_id' => $category->entityId(),
        ]);

        $form->validateResolved();

        $this->assertNotEmpty($form->validated());
    }
}
