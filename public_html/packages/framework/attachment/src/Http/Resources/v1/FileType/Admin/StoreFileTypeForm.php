<?php

namespace MetaFox\Attachment\Http\Resources\v1\FileType\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Core\Models\AttachmentFileType as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreFileTypeForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreFileTypeForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action(apiUrl('admin.attachment.type.store'))
            ->asPost()
            ->setValue([
                'is_active' => 1,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('extension')
                    ->required()
                    ->label(__p('attachment::phrase.file_extension'))
                    ->yup(Yup::string()->required()),
                Builder::text('mime_type')
                    ->required()
                    ->label(__p('attachment::phrase.file_mime_type'))
                    ->yup(Yup::string()->required()),
                Builder::switch('is_active')
                    ->label(__p('core::phrase.is_active')),
            );

        $this->addDefaultFooter($this->isEdit());
    }

    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }
}
