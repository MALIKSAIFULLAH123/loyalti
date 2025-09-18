<?php

namespace MetaFox\GettingStarted\Rules;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;
use MetaFox\GettingStarted\Support\Helper;
use MetaFox\Platform\MetaFoxConstant;

class MaxItemResolutionRule implements RuleContract
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        if ($attribute !== 'resolution' || !in_array($value, [MetaFoxConstant::RESOLUTION_WEB, MetaFoxConstant::RESOLUTION_MOBILE])) {
            return false;
        }

        $totalItems = $this->todoListRepository()->countTodoList(['resolution' => $value]);

        if ($totalItems >= Helper::MAX_ITEMS) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return __p('getting-started::phrase.the_maximum_number_of_items_has_been_reached');
    }

    protected function todoListRepository()
    {
        return resolve(TodoListRepositoryInterface::class);
    }
}
