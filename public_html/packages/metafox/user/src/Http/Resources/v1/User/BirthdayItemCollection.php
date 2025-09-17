<?php

namespace MetaFox\User\Http\Resources\v1\User;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class BirthdayItemCollection extends ResourceCollection
{
    public $collects = BirthdayItem::class;
}
