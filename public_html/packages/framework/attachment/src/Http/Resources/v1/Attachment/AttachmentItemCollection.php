<?php

namespace MetaFox\Attachment\Http\Resources\v1\Attachment;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class AttachmentItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class AttachmentItemCollection extends ResourceCollection
{
    public $collects = AttachmentItem::class;
}
