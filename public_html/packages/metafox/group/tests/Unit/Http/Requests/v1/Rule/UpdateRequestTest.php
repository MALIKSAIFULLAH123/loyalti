<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Rule;

use MetaFox\Group\Http\Requests\v1\Rule\UpdateRequest as Request;
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

    public function provideRequests()
    {
        return $this->makeRequests(
            $this->shouldRequire('title'),
            $this->failIf('title', 0, null, str_pad('A', 1000, 'A')),
            $this->failIf('description', 0, str_pad('A', 1000, 'A')),
        );
    }

    public function testSuccess()
    {
        $form = $this->buildForm([
            'title'       => $this->faker->title,
            'description' => $this->faker->text,
        ]);

        $form->validateResolved();
        $this->assertNotEmpty($form->validated());
    }
}
