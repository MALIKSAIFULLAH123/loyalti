<?php

namespace MetaFox\Group\Http\Resources\v1\ExampleRule;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class ExampleRuleItemCollection.
 */
class ExampleRuleItemCollection extends ResourceCollection
{
    public $collects = ExampleRuleItem::class;
}
