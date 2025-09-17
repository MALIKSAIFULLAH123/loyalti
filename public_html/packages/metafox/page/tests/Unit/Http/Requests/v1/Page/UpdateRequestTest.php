<?php

namespace MetaFox\Page\Tests\Unit\Http\Requests\v1\Page;

use MetaFox\Page\Http\Requests\v1\Page\UpdateRequest as Request;
use MetaFox\Page\Models\Category;
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

        $form = $this->buildForm([
            'name'        => $this->faker->name,
            'category_id' => $category->id,
            'text'        => $this->faker->text,
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->failIf('name', 0, null, str_pad('A', 1000, 'A')),
            $this->failIf('category_id', 0, 'string'),
            $this->failIf('text', 0),
            $this->failIf('vanity_url', 0, [], new \stdClass()),
            $this->failIf('landing_page', 0, []),
            $this->failIf('phone', 0, []),
            $this->failIf('external_link', 0, []),
            $this->failIf('location', 0, 'string'),
        );
    }

    protected function beforeTest()
    {
        $user = $this->createNormalUser();
        $this->be($user);
    }
}
