<?php

namespace MetaFox\Page\Tests\Unit\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Page\Rules\DeletePageCategoryRule;
use Tests\TestCase;

class DeletePageCategoryRuleTest extends TestCase
{
    /**
     * @throws ValidationException
     */
    public function testValidateSuccess(): void
    {
        $data = [
            'new_category_id' => 1,
        ];
        $validator = Validator::make($data, [
            'new_category_id' => [
                new DeletePageCategoryRule('new_category_id', 'new_type_id', null),
            ],
        ]);
        $this->assertIsArray($validator->validate());
    }

    /**
     * @throws ValidationException
     */
    public function testValidateFail(): void
    {
        $typeValue = 1;
        $data = [
            'new_category_id' => 1,
            'new_type_id'     => $typeValue,
        ];
        $validator = Validator::make($data, [
            'new_category_id' => [
                new DeletePageCategoryRule('new_category_id', 'new_type_id', $typeValue),
            ],
        ]);

        $this->expectException(ValidationException::class);
        $validator->validate();
    }

    /**
     * @throws ValidationException
     */
    public function testValidateSuccess2(): void
    {
        $typeValue = 1;
        $data = [
            'new_category_id' => 0,
            'new_type_id'     => $typeValue,
        ];
        $validator = Validator::make($data, [
            'new_category_id' => [
                new DeletePageCategoryRule('new_category_id', 'new_type_id', $typeValue),
            ],
        ]);

        $this->assertIsArray($validator->validate());
    }
}
