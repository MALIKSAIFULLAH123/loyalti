<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\Page;

use MetaFox\Page\Http\Requests\v1\Page\StoreRequest as Request;
use MetaFox\Page\Models\Category;
use Tests\TestFormRequest;

/**
 * Class StoreRequestTest.
 */
class StoreRequestTest extends TestFormRequest
{
    public function requestName(): string
    {
        return Request::class;
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('name'),
            $this->failIf('name', 0, null),
            $this->failIf('category_id', 0, 'string'),
            $this->failIf('text', 0),
            $this->failIf('users', 0, 'string', [['id' => 0]]),
        );
    }

    public function testSuccess()
    {
        $this->beforeTest();

        $category = Category::factory()->create();

        $form = $this->buildForm([
            'name'        => $this->faker->name,
            'category_id' => $category->entityId(),

        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    protected function beforeTest()
    {
        $user = $this->createNormalUser();
        $this->be($user);
    }
}
