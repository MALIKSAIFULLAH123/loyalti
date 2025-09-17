<?php

namespace MetaFox\Marketplace\Policies;

use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Traits\Policy\HasCategoryPolicyTrait;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class CategoryPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 */
class CategoryPolicy implements ResourcePolicyInterface
{
    use HasCategoryPolicyTrait;
    use HasPolicyTrait;

    protected string $type = 'marketplace_category';

    //
}
