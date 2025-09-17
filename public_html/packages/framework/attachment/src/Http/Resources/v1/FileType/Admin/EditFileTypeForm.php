<?php

namespace MetaFox\Attachment\Http\Resources\v1\FileType\Admin;

use MetaFox\Core\Models\AttachmentFileType as Model;
use MetaFox\Core\Repositories\AttachmentFileTypeRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class EditFileTypeForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditFileTypeForm extends StoreFileTypeForm
{
    public function boot(AttachmentFileTypeRepositoryInterface $typeRepository, int $type = 0): void
    {
        $this->resource = $typeRepository->find($type);
    }

    protected function prepare(): void
    {
        $this->title(__p('attachment::phrase.edit_type'))
            ->action(apiUrl('admin.attachment.type.update', ['type' => $this->resource->entityId()]))
            ->asPut()
            ->setValue([
                'extension' => $this->resource->extension,
                'mime_type' => $this->resource->mime_type,
                'is_active' => $this->resource->is_active,
            ]);
    }
}
