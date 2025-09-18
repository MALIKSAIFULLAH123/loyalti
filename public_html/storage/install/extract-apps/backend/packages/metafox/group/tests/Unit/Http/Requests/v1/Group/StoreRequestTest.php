<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Group;

use MetaFox\Group\Http\Requests\v1\Group\StoreRequest as Request;
use MetaFox\Group\Models\Category;
use MetaFox\User\Models\User;
use Tests\TestCase;
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
            $this->failIf('name', 0, null, [], 'A', str_pad('A', 500, 'A')),
            $this->failIf('category_id', 0, null, [], 'string'),
            $this->failIf('reg_method', null, 'string', [], -1, 4),
            $this->failIf('text', 0, [], new \stdClass()),
            $this->failIf('users', 0, 'string', null, [['id' => 0]]),
            $this->passIf('name', uniqid('group-name-')),
            $this->passIf('reg_method', 0, 1, 2)
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->makeOne();
        $this->be($user);

        $this->mockSiteSettings([
            'group.minimum_name_length' => 10,
            'group.maximum_name_length' => 100,
        ]);
    }

    public function testSuccess(): Category
    {
        /** @var Category $category */
        $category = Category::factory()->makeOne(['id' => 1, 'is_active' => 1]);

        $form = $this->buildForm([
            'name'        => uniqid('group-name-'),
            'reg_method'  => 0,
            'category_id' => $category->entityId(),
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());

        return $category;
    }
}
