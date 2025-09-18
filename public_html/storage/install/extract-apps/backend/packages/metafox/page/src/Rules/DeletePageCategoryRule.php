<?php

namespace MetaFox\Page\Rules;

use Illuminate\Contracts\Validation\Rule;

class DeletePageCategoryRule implements Rule
{
    private string $field1;
    private string $field2;
    private ?int $valueField2;

    /**
     * @return string
     */
    public function getField1(): string
    {
        return $this->field1;
    }

    /**
     * @return string
     */
    public function getField2(): string
    {
        return $this->field2;
    }

    /**
     * @return int|null
     */
    public function getValueField2(): ?int
    {
        return $this->valueField2;
    }

    /**
     * AllowInRule constructor.
     *
     * @param string   $field1
     * @param string   $field2
     * @param int|null $valueField2
     */
    public function __construct(string $field1, string $field2, ?int $valueField2)
    {
        $this->field1 = $field1;
        $this->field2 = $field2;
        $this->valueField2 = $valueField2;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if ($this->getValueField2() == null) {
            return true;
        }

        if ($value > 0 && $this->getValueField2() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __p('page::validation.accept_only_1_of_2_values', [
            'field1' => $this->getField1(),
            'field2' => $this->getField2(),
        ]);
    }
}
