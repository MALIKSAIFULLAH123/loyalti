<?php

namespace MetaFox\Profile\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * Class MinOptionsRule.
 */
class MinOptionsRule implements Rule, DataAwareRule
{
    public function __construct()
    {
    }

    /**
     * @var array
     */
    protected array  $data = [];
    protected string $attribute;
    const MIN_OPTIONS = 2;

    /**
     * Set the data under validation.
     *
     * @param array $data
     *
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

        if (!in_array($value, CustomFieldFacade::getEditTypeAllowOptions())) {
            return true;
        }

        $options = Arr::get($params, 'options');
        if (count($options) < self::MIN_OPTIONS) {
            return false;
        }

        $statusRemove = collect($options)->filter(function ($option) {
            return $option['status'] == MetaFoxConstant::FILE_REMOVE_STATUS;
        });

        if (count($statusRemove) >= count($options)) {
            return false;
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
        return __p('profile::validation.please_provide_at_least_two_options', ['number' => self::MIN_OPTIONS]);
    }
}
