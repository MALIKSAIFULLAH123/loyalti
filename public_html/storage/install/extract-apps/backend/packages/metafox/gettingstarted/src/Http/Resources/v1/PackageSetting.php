<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\GettingStarted\Http\Resources\v1;

use MetaFox\GettingStarted\Repositories\TodoListRepositoryInterface;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getWebSettings(): array
    {
        return [
            'has_todo_list' => resolve(TodoListRepositoryInterface::class)->countTodoList(['resolution' => 'web']) > 0,
        ];
    }

    public function getMobileSettings(): array
    {
        return [
            'has_todo_list' => resolve(TodoListRepositoryInterface::class)->countTodoList(['resolution' => 'mobile']) > 0,
        ];
    }
}
