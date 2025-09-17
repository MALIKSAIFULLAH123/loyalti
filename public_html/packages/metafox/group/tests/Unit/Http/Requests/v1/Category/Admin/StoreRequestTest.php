<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Category\Admin;

use Illuminate\Validation\ValidationException;
use MetaFox\Group\Http\Requests\v1\Category\Admin\StoreRequest;
use MetaFox\Group\Models\Category;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return StoreRequest::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('name'),
            $this->failIf('parent_id', 0, 'string', []),
            $this->withSampleParameters('is_active', 'ordering')
        );
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

    public function testNameRequired()
    {
        $form = $this->buildForm([
            'name' => null,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testNameString()
    {
        $form = $this->buildForm([
            'name' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testNameMin()
    {
        $form = $this->buildForm([
            'name' => 'Te',
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testParentIdNumber()
    {
        $form = $this->buildForm([
            'name'      => $this->faker->name,
            'parent_id' => 'Test',
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testParentIdExist()
    {
        $form = $this->buildForm([
            'name'      => $this->faker->name,
            'parent_id' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testIsActiveNumber()
    {
        $form = $this->buildForm([
            'name'      => $this->faker->name,
            'is_active' => 'Test',
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOrderingNumber()
    {
        $form = $this->buildForm([
            'name'     => $this->faker->name,
            'ordering' => 'Test',
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
