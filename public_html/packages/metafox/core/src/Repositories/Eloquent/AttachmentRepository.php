<?php

namespace MetaFox\Core\Repositories\Eloquent;

use Illuminate\Http\UploadedFile;
use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Core\Models\Attachment;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * @method Attachment getModel()
 * @method Attachment find($id, $columns = ['*'])()
 */
class AttachmentRepository extends AbstractRepository implements AttachmentRepositoryInterface
{
    public function model(): string
    {
        return Attachment::class;
    }

    public function updateItemId(?array $attachments, HasTotalAttachment $item): bool
    {
        if (empty($attachments)) {
            return true;
        }

        $attachments       = collect($attachments)->groupBy('status');
        $newAttachments    = $attachments->get(MetaFoxConstant::FILE_NEW_STATUS);
        $createAttachments = $attachments->get(MetaFoxConstant::FILE_CREATE_STATUS);

        if ($createAttachments?->isNotEmpty()) {
            $newAttachments = $createAttachments;
        }

        // Getting new attachments by 'new' status
        $newIds = $newAttachments ? $newAttachments->pluck('id')->toArray() : [];

        // Getting removed attachments by 'remove' status
        $removedAttachments = $attachments->get(MetaFoxConstant::FILE_REMOVE_STATUS);
        $removeIds          = $removedAttachments ? $removedAttachments->pluck('id')->toArray() : [];

        if (!empty($newIds)) {
            foreach ($newIds as $newId) {
                $this->getModel()->newQuery()
                    ->where('id', $newId)
                    ->update(['item_id' => $item->entityId()]);
            }
        }

        if (!empty($removeIds)) {
            $this->deleteAttachments($removeIds);
        }

        if (!empty($newIds) || !empty($removeIds)) {
            $this->updateTotalAttachment($item);
        }

        return true;
    }

    /**
     * @param HasTotalAttachment $item
     */
    private function updateTotalAttachment(HasTotalAttachment $item): void
    {
        $totalAttachment = $this->getModel()->newQuery()
            ->where('item_id', $item->entityId())
            ->where('item_type', $item->entityType())
            ->count('id');

        $item->update(['total_attachment' => $totalAttachment]);
    }

    /**
     * @param int[] $ids
     *
     * @return bool
     */
    private function deleteAttachments(array $ids): bool
    {
        if (empty($ids)) {
            return false;
        }

        // boot to attachment to delete file later.

        $this->getModel()->newQuery()
            ->whereIn('id', $ids)
            ->delete();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function verifyAttachmentType(User $context, UploadedFile $file, string $itemType): bool
    {
        $allowTypes = '*';

        try {
            $allowTypes = $context->getPermissionValue($itemType . '.attachment_type_allow');
        } catch (\Exception $err) {
            // Silent the exception
        }

        if ('*' === $allowTypes) {
            return true;
        }

        $allowTypes   = explode(',', $allowTypes);
        $fileMimeType = $file->getMimeType();

        foreach ($allowTypes as $type) {
            if (!is_string($type)) {
                continue;
            }

            $pattern = str_replace('/*', '\/(.*)', trim($type));

            if (preg_match("/$pattern/m", $fileMimeType)) {
                return true;
            }
        }

        return false;
    }
}
