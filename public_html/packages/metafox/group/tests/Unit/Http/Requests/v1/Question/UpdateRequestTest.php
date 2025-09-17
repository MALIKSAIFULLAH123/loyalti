<?php

namespace MetaFox\Group\Tests\Unit\Http\Requests\v1\Question;

use Illuminate\Validation\ValidationException;
use MetaFox\Group\Http\Requests\v1\Question\UpdateRequest as Request;
use MetaFox\Group\Models\Question;
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
            $this->failIf('type_id', null, 'string', -1, 3),
            $this->passIf('type_id', 0, 1, 2),
            $this->failIf('question', null),
            $this->passIf('question', 'any string '),
        );
    }

    public function testOptionsArray()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => 'test',
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOptionsNewArray()
    {
        $form = $this->buildForm([
            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'new' => 'test',
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOptionsNewArrayString()
    {
        $form = $this->buildForm([

            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'new' => [1],
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOptionsUpdateArray()
    {
        $form = $this->buildForm([

            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'update' => 'test',
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOptionsUpdateIdNumeric()
    {
        $form = $this->buildForm([

            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'update' => [
                    [
                        'id'    => 'test',
                        'title' => 'test',
                    ],
                ],
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOptionsRemoveArray()
    {
        $form = $this->buildForm([

            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'remove' => 'test',
                'new'    => 'new test',
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }

    public function testOptionsRemoveArrayIdExist()
    {
        $form = $this->buildForm([

            'question' => $this->faker->title,
            'type_id'  => Question::TYPE_SELECT,
            'options'  => [
                'remove' => [0],
            ],
        ]);

        $this->expectException(ValidationException::class);
        $form->validateResolved();
    }
}
