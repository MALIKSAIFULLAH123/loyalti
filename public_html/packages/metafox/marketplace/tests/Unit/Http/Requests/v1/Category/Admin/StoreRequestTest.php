<?php

namespace MetaFox\Marketplace\Tests\Unit\Http\Requests\v1\Category\Admin;

use MetaFox\Marketplace\Http\Requests\v1\Category\Admin\StoreRequest as Request;
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
            $this->failIf('name', null, 0, 'AA', str_pad('A', 256, 'A')),
            $this->failIf('parent_id', 0, 'string'),
            $this->failIf('is_active', 'string', null),
            $this->failIf('ordering', 'string', null),
        );
    }

    public function testSuccess()
    {
        $form = $this->buildForm([
            'name'      => $this->faker->name,
            'is_active' => 1,
            'ordering'  => 1,

        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }
}
