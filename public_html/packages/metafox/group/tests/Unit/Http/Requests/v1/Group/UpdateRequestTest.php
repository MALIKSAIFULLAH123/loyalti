<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Group;

use MetaFox\Group\Http\Requests\v1\Group\UpdateRequest as Request;
use MetaFox\Group\Models\Category;
use MetaFox\Group\Support\PrivacyTypeHandler;
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

    public function setUp(): void
    {
        parent::setUp();

        $this->mockSiteSettings([
            'group.minimum_name_length' => 2,
            'group.maximum_name_length' => 255,
        ]);
    }

    public function testSuccess()
    {
        $category = Category::factory()->create();
        $form     = $this->buildForm([
            'name'        => uniqid('group_'),
            'reg_method'  => PrivacyTypeHandler::PUBLIC,
            'category_id' => $category->entityId(),
            'text'        => $this->faker->text,
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->failIf('name', 0, null, 'g', str_pad('group ', 1000, 'name')),
            $this->failIf('category_id', 'string', null, 0),
            $this->failIf('reg_method', 'string', -1),
            $this->failIf('text', 0),
            $this->failIf('landing_page', -1, null),
            $this->passIf('reg_method', 0, 1, 2),
        );
    }
}
