<?php

namespace MetaFox\Group\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Support\Facades\Group as GroupFacade;

/**
 * Class ConfirmRule.
 * @ignore
 */
class ConfirmRule implements RuleContract
{
    /**
     * @var Group
     */
    private $group;

    /**
     * @param Group|null $group
     */
    public function __construct(?Group $group = null)
    {
        $this->group = $group;
    }

    /**
     * @param  Group $group
     * @return void
     */
    public function setGroup(Group $group): void
    {
        $this->group = $group;
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (null === $this->group) {
            return false;
        }

        if ($this->mustAcceptRule() === false) {
            return true;
        }

        return $value == 1;
    }

    public function mustAcceptRule()
    {
        return GroupFacade::mustAcceptGroupRule($this->group) === true;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return __p('group::phrase.you_must_accept_group_rules');
    }
}
