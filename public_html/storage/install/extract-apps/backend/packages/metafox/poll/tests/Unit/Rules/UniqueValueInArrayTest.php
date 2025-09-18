<?php

namespace MetaFox\Poll\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Platform\Rules\UniqueValueInArray;
use Tests\TestCase;

class UniqueValueInArrayTest extends TestCase
{
    /**
     * @throws ValidationException
     */
    public function testValidateSuccess(): void
    {
        $data = [
            'answers' => [
                ['answer' => 'answer 1'],
                ['answer' => 'answer 2'],
                ['answer' => 'answer 3'],
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new UniqueValueInArray(['answer']),
            ],
        ]);
        $this->assertIsArray($validator->validate());
    }

    /**
     * @throws ValidationException
     */
    public function testValidateFail(): void
    {
        $data = [
            'answers' => [
                ['answer' => 'answer 1'],
                ['answer' => 'answer 1'],
                ['answer' => 'answer 3'],
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new UniqueValueInArray(['answer']),
            ],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }
}
