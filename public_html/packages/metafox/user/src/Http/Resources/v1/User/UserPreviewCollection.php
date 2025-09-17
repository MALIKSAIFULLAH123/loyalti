<?php

namespace MetaFox\User\Http\Resources\v1\User;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class UserPreviewCollection extends ResourceCollection
{
    public $collects = UserPreview::class;
}
