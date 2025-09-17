<?php

namespace MetaFox\User\Http\Resources\v1\UserPromotion\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserPromotionItemCollection extends ResourceCollection
{
    public $collects = UserPromotionItem::class;
}
