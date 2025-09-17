<?php

namespace MetaFox\Comment\Http\Resources\v1\CommentAttachment;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class CommentAttachmentItemCollection extends ResourceCollection
{
    public $collects = CommentAttachmentItem::class;
}
