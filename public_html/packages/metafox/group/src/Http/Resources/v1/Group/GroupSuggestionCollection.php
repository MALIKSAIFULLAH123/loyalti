<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class GroupSuggestionCollection extends ResourceCollection
{
    public $collects = GroupSuggestion::class;
}
