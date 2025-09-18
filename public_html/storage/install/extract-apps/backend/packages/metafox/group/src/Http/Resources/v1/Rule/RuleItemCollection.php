<?php

namespace MetaFox\Group\Http\Resources\v1\Rule;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class RuleItemCollection.
 */
class RuleItemCollection extends ResourceCollection
{
    public $collects = RuleItem::class;
}
