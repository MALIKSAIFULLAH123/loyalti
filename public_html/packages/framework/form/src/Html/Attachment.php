<?php

namespace MetaFox\Form\Html;

use MetaFox\Core\Support\Facades\AttachmentFileType;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * Class Attachment.
 */
class Attachment extends File
{
    public function initialize(): void
    {
        $accepts = AttachmentFileType::getAllExtensionActive();

        if (empty($accepts)) {
            $this->setComponent(MetaFoxForm::HIDDEN)
                ->name('attachments');
            return;
        }

        $maxUploadFileSize = Settings::get('core.attachment.maximum_file_size_each_attachment_can_be_uploaded'); //in byte

        $this->component(MetaFoxForm::ATTACHMENT)
            ->name('attachments')
            ->label(__p('core::phrase.attachment'))
            ->placeholder(__p('core::phrase.attach_files'))
            ->variant('outlined')
            ->fullWidth()
            ->maxUploadSize($maxUploadFileSize)
            ->uploadUrl('/attachment')
            ->multiple(true)
            ->storageId('attachment');

        $this->setAttribute('accept', '.' . implode(',.', $accepts));
        $this->handleYup();
    }

    protected function handleYup(): void
    {
        $accepts  = AttachmentFileType::getAllExtensionActive();
        $maxFiles = Settings::get('core.attachment.maximum_number_of_attachments_that_can_be_uploaded');
        $yup      = Yup::array()
            ->of(Yup::object()
                ->addProperty('id', Yup::number()->required())
                ->addProperty('file_name', Yup::string()->required())
                ->addProperty('_destroy', Yup::number())
                ->addProperty('_new', Yup::number())
                ->addProperty('extension', Yup::string()
                    ->oneOf($accepts)
                    ->setError('oneOf', __p('validation.mimes', [
                        'attribute' => 'file',
                        'values'    => implode(', ', $accepts),
                    ])))
            );

        if ($maxFiles > 0) {
            $yup->maxWhen([
                'value' => $maxFiles,
                'when'  => ['eq', 'item.status', MetaFoxConstant::FILE_NEW_STATUS],
            ], __p('core::phrase.maximum_per_upload_limit_reached', ['limit' => $maxFiles]))
                ->of(
                    Yup::object()
                        ->addProperty('id', Yup::number())
                        ->addProperty('type', Yup::string())
                        ->addProperty('status', Yup::string())
                );
        }

        $this->yup($yup);
    }
}
