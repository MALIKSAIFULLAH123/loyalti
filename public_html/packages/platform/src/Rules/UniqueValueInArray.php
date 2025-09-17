<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueValueInArray implements Rule
{
    /**
     * @var array<string>
     */
    private array $fields;

    /**
     * UniqueValueInArray constructor.
     * @param array<string> $uniqueFields
     */
    public function __construct(array $uniqueFields)
    {
        $this->fields = $uniqueFields;
    }

    /**
     * Will validate the uniqueness against field's value when it's formatted as the following example:
     * <code>
     * [
     *      ['field' => 'value 1', ...],
     *      ['field' => 'value 2', ...],
     * ]
     * </code>
     * The 'field' must be included when initiating the Rule.
     *
     * @param  string $attribute
     * @param  mixed  $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($this->fields as $field) {
            $fieldValues = array_column($value, $field);
            $duplicates = [];
            foreach ($fieldValues as $fieldValue) {
                if (!$fieldValue) {
                    continue;
                }

                $duplicates[$fieldValue] = isset($duplicates[$fieldValue]) ? ++$duplicates[$fieldValue] : 1;
                if ($duplicates[$fieldValue] > 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('validation.the_attribute_list_must_be_unique');
    }
}
