<?php

namespace MetaFox\Photo\Http\Resources\v1\Photo;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class PhotoTaggedFriendCollection extends ResourceCollection
{
    public $collects = PhotoTaggedFriend::class;
}
