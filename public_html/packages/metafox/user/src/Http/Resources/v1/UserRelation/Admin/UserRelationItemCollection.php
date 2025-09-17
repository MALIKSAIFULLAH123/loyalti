<?php

namespace MetaFox\User\Http\Resources\v1\UserRelation\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserRelationItemCollection extends ResourceCollection
{
    public $collects = UserRelationItem::class;
}
