<?php

namespace Foxexpert\Sevent\Policies;

use Foxexpert\Sevent\Models\Category;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Traits\Policy\HasCategoryPolicyTrait;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class CategoryPolicy.
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use HasCategoryPolicyTrait;

    protected string $type = Category::class;
}
