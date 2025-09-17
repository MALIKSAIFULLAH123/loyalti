<?php

namespace MetaFox\Attachment\Traits;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Core\Models\Attachment;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\ResourcePermission;

/**
 * Trait CommentExtraTrait.
 * @property Attachment $resource
 */
trait HasAttachmentExtraTrait
{
    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getExtra(): array
    {
        $context = user();

        return [
            ResourcePermission::CAN_DOWNLOAD => PolicyGate::check($this->resource->item_type, 'downloadAttachment', [$context, $this->resource]),
        ];
    }
}
