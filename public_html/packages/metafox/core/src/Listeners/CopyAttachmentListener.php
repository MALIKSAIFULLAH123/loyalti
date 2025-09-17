<?php

namespace MetaFox\Core\Listeners;

use MetaFox\Core\Models\Attachment;
use MetaFox\Platform\Contracts\User;

class CopyAttachmentListener
{
    public function handle(?User $context, Attachment $attachment, ?int $itemId, ?string $itemType): ?Attachment
    {
        return $attachment->clone($context, $itemType, $itemId);
    }
}
