<?php

namespace MetaFox\Video\Policies;

use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Traits\Policy\HasCategoryPolicyTrait;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;
use MetaFox\Video\Models\Category;

/**
 * Class CategoryPolicy.
 * @SuppressWarnings(PHPMD)
 * @ignore
 */
class CategoryPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use HasCategoryPolicyTrait;

    protected string $type = Category::class;
}
