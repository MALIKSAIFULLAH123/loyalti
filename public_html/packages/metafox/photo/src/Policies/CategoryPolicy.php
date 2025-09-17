<?php

namespace MetaFox\Photo\Policies;

use MetaFox\Photo\Models\Category;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Traits\Policy\HasCategoryPolicyTrait;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

class CategoryPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use HasCategoryPolicyTrait;

    protected string $type = Category::class;
}
