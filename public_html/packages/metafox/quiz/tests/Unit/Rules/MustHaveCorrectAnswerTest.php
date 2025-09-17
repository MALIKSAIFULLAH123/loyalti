<?php

namespace MetaFox\Quiz\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Quiz\Rules\MustHaveCorrectAnswer;
use Tests\TestCase;

class MustHaveCorrectAnswerTest extends TestCase
{
    /**
     * @throws ValidationException
     */
    public function testValidateSuccessWithOneCorrectAnswer(): void
    {
        $data = [
            'answers' => [
                ['answer' => 'answer 1', 'is_correct' => 1],
                ['answer' => 'answer 2'],
                ['answer' => 'answer 3'],
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new MustHaveCorrectAnswer('is_correct'),
            ],
        ]);
        $this->assertIsArray($validator->validate());
    }

    /**
     * @throws ValidationException
     */
    public function testValidateSuccessWithMoreThanOneCorrectAnswer(): void
    {
        $data = [
            'answers' => [
                ['answer' => 'answer 1', 'is_correct' => 1],
                ['answer' => 'answer 2'],
                ['answer' => 'answer 3', 'is_correct' => 1],
                ['answer' => 'answer 4'],
                ['answer' => 'answer 5', 'is_correct' => 1],
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new MustHaveCorrectAnswer('is_correct'),
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
                ['answer' => 'answer 1', 'correct' => 1],
                ['answer' => 'answer 2'],
                ['answer' => 'answer 3'],
            ],
        ];
        $validator = Validator::make($data, [
            'answers' => [
                new MustHaveCorrectAnswer('is_correct'),
            ],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }
}
