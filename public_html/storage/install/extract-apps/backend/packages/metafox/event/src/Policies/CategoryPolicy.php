<?php

namespace MetaFox\Event\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Traits\Policy\HasCategoryPolicyTrait;
use MetaFox\Platform\Traits\Policy\HasPolicyTrait;

/**
 * Class CategoryPolicy.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CategoryPolicy implements ResourcePolicyInterface
{
    use HasPolicyTrait;
    use HandlesAuthorization;
    use HasCategoryPolicyTrait;

    protected string $type = 'event_category';

    //
}
