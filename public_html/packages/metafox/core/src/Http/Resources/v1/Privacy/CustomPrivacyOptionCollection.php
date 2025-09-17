<?php

namespace MetaFox\Core\Http\Resources\v1\Privacy;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CustomPrivacyOptionCollection extends ResourceCollection
{
    public $collects = CustomPrivacyOptionItem::class;
}
