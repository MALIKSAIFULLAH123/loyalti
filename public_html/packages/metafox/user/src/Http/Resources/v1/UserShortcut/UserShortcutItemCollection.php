<?php

namespace MetaFox\User\Http\Resources\v1\UserShortcut;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserShortcutItemCollection extends ResourceCollection
{
    public $collects = UserShortcutItem::class;
}
