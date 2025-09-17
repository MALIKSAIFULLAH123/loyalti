<?php

namespace MetaFox\Core\Repositories;

use Illuminate\Http\UploadedFile;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Models\Attachment;
use MetaFox\Platform\Contracts\User;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Interface Attachment.
 *
 * @mixin BaseRepository
 * @method Attachment getModel()
 * @method Attachment find($id, $columns = ['*'])()
 */
interface AttachmentRepositoryInterface
{
    /**
     * <pre>.
     * Attachments array must contain attachment id and "status"
     * status = "remove" : remove attachment from item
     * status = "new" : insert new attachment to item
     * </pre>
     *
     * @param array<mixed>       $attachments
     * @param HasTotalAttachment $item
     *
     * @return bool
     */
    public function updateItemId(?array $attachments, HasTotalAttachment $item): bool;

    /**
     * @param User         $context
     * @param UploadedFile $file
     * @param string       $itemType
     *
     * @return bool
     */
    public function verifyAttachmentType(User $context, UploadedFile $file, string $itemType): bool;
}
