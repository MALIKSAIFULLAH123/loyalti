<?php

namespace MetaFox\Profile\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use MetaFox\Profile\Repositories\FieldRepositoryInterface;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * Class OptionsFieldRule.
 */
class OptionsFieldRule implements Rule, DataAwareRule
{
    public function __construct(protected ?int $fieldId = null)
    {
    }

    /**
     * @var array
     */
    protected array  $data = [];
    protected string $attribute;

    /**
     * Set the data under validation.
     *
     * @param array $data
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;
        $params          = $this->data;
        $editType        = Arr::get($params, 'edit_type');

        if ($this->fieldId != null) {
            /**@var FieldRepositoryInterface $fieldRepository */
            $fieldRepository = resolve(FieldRepositoryInterface::class);
            $editType        = $fieldRepository->find($this->fieldId)?->edit_type;
        }

        if (!in_array($editType, CustomFieldFacade::getEditTypeAllowOptions())) {
            return true;
        }

        if (empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('profile::validation.field_label_option_is_a_required_field');
    }
}
