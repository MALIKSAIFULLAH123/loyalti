<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Models\Attachment;

/**
 * Trait HasTotalAttachment.
 * @mixin Model
 * @mixin HasTotalAttachment
 */
trait HasTotalAttachmentTrait
{
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'item', 'item_type', 'item_id', $this->primaryKey);
    }

    public function attachmentsForForm(): array
    {
        return $this->attachments->map(function (Attachment $attachment) {
            return [
                'id'        => $attachment->entityId(),
                'file_name' => $attachment->file_name_with_extension,
            ];
        })->toArray();
    }
}
