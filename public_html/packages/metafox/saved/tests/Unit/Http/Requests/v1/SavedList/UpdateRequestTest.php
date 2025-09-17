<?php

namespace MetaFox\Saved\Tests\Unit\Http\Requests\v1\SavedList;

use Illuminate\Validation\ValidationException;
use MetaFox\Saved\Http\Requests\v1\SavedList\UpdateRequest as Request;
use Tests\TestFormRequest;

/**
 * --------------------------------------------------------------------------
 *  Http request for test
 * --------------------------------------------------------------------------.
 *
 * Assign this class in $controllers of
 * @link \MetaFox\Saved\Http\Controllers\Api\SavedListController::$controllers;
 * stub: api_action_request_test.stub
 */

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
            $this->shouldRequire('name'),
            $this->withSampleParameters('privacy'),
            $this->failIf('name', 0, null, [], str_pad('A', 500, 'A')),
        );
    }

    public function testSuccess()
    {
        $form = $this->buildForm([
            'name' => 'test',
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

    public function testFailNameTypeNumeric()
    {
        $form = $this->buildForm([
            'name' => 123,
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testNameMax()
    {
        $form = $this->buildForm([
            'name' => $this->faker->words(200),
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
