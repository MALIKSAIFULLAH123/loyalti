<?php

namespace MetaFox\Group\Policies;

use MetaFox\Group\Models\Category as Resource;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Traits\Policy\HasCategoryPolicyTrait;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class CategoryPolicy.
 * @ignore
 */
class CategoryPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use HasCategoryPolicyTrait;

    protected string $type = Resource::ENTITY_TYPE;
}
