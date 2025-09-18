<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Category\Admin;

use MetaFox\Group\Http\Requests\v1\Category\Admin\UpdateRequest as Request;
use MetaFox\Group\Models\Category;
use Tests\TestCase;
use Tests\TestFormRequest;

/**
 * Class UpdateRequestTest.
 */
class UpdateRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function testSuccess()
    {
        $category = Category::factory()->create();
        $form     = $this->buildForm([
            'name'      => $this->faker->name,
            'type_id'   => 0, //Todo: check if NamNV confirm,
            'is_active' => 1,
            'ordering'  => 1,
            'parent_id' => $category->entityId(),

        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->failIf('name', 0, 'a', str_pad('a', 500, 'a')),
            $this->failIf('parent_id', 0, 'string'),
            $this->failIf('is_active', 'string'),
            $this->failIf('ordering', 'string'),
        );
    }
}
