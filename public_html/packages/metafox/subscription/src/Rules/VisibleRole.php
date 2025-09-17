<?php

namespace MetaFox\Subscription\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Subscription\Support\Facade\SubscriptionPackage as Facade;

class VisibleRole implements
    Rule,
    ValidatorAwareRule,
    DataAwareRule,
    ImplicitRule
{
    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var SubscriptionPackage|null
     */
    protected $resource;

    public function __construct(?SubscriptionPackage $resource = null)
    {
        $this->resource = $resource;
    }

    public function passes($attribute, $value)
    {
        if (null === $value) {
            return true;
        }

        $upgradedPackages = Arr::get($this->data, 'upgraded_package_id');

        if (is_array($upgradedPackages)) {
            return true;
        }

        if (!$this->validator->validateArray($attribute, $value)) {
            return false;
        }

        $upgradedRoleId = $this->getUpgradedRoleId();

        if (!$upgradedRoleId) {
            return false;
        }

        foreach ($value as $role) {
            if ($role == $upgradedRoleId) {
                return false;
            }

            $role = (int) $role;

            if (!$this->validator->validateExists('visible_roles.*', $role, ['auth_roles', 'id'])) {
                return false;
            }
        }

        return true;
    }

    protected function getUpgradedRoleId(): int
    {
        $hasDisabledFields = null !== $this->resource ? Facade::hasDisableFields($this->resource->entityId()) : false;

        if ($hasDisabledFields) {
            $upgradedRoleId = $this->resource->upgraded_role_id;
        } else {
            $upgradedRoleId = Arr::get($this->data, 'upgraded_role_id');
        }

        if (!$upgradedRoleId) {
            return 0;
        }

        return $upgradedRoleId;
    }

    public function message(): string
    {
        return __p('subscription::validation.visible_role_must_be_different_from_user_role_on_success');
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;
    }
}
