<?php

namespace MetaFox\Subscription\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class DowngradePackage implements
    Rule,
    DataAwareRule,
    ValidatorAwareRule,
    ImplicitRule
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var Validator
     */
    protected $validation;

    public function passes($attribute, $value)
    {
        if (null === $value) {
            return true;
        }

        if (!$this->validation->validateNumeric($attribute, $value)) {
            return false;
        }

        if (!$this->validation->validateExists($attribute, $value, ['subscription_packages', 'id'])) {
            return false;
        }

        $data = $this->data;

        $upgradePackageIds = [];

        if (Arr::has($data, 'upgraded_package_id') && is_array($data['upgraded_package_id'])) {
            $upgradePackageIds = Arr::get($data, 'upgraded_package_id');
        }

        return $this->validation->validateNotIn($attribute, $value, $upgradePackageIds);
    }

    public function message()
    {
        return __p('subscription::validation.downgrade_package_must_be_numeric_and_not_existed_in_upgrade_packages');
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setValidator($validator)
    {
        $this->validation = $validator;
    }
}
