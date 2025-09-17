<?php

namespace MetaFox\Ban\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Closure;
use Illuminate\Support\Arr;
use MetaFox\Ban\Repositories\BanRuleRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;

class UniqueBanRuleRule implements ValidationRule, DataAwareRule
{
    /**
     * @var array
     */
    protected mixed $data = null;

    /**
     * @param       $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || $value === MetaFoxConstant::EMPTY_STRING) {
            return;
        }

        if (!resolve(BanRuleRepositoryInterface::class)->isExistBanRule($value, Arr::get($this->data, 'type'))) {
            return;
        }

        $fail(__p('ban::validation.ban_rule_has_already_existed'));
    }
}
