<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Image;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Html\File;
use MetaFox\Marketplace\Models\Image;
use MetaFox\Marketplace\Models\Listing;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * @property Listing $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UploadImageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $order = $this->resource->photos?->map(function (Image $image) {
            return $image->id;
        });

        $value = [
            'order'   => $order,
            'default' => 0, //todo find way to get default image
        ];

        $this->config([
            'title'  => 'Editing Listing',
            'action' => url_utility()->makeApiUrl('marketplace-photo/' . $this->resource->entityId()),
            'method' => MetaFoxForm::METHOD_PUT,
            'value'  => $value,
        ]);
    }

    public function initialize(): void
    {
        $currentFiles = $this->resource->photos?->map(function (Image $image) {
            return [
                'id'      => $image->id,
                'url'     => $image->image_path,
                'default' => false, //todo find way to check default image or not
            ];
        });
        $basic = $this->addBasic();
        $basic->addFields(
            new File([
                'name'                => 'files',
                'component'           => 'MultiFile',
                'multiple'            => true,
                'label'               => 'Photos',
                'min_files'           => 0,
                'max_files'           => 6,
                'file_type'           => 'photo',
                'item_type'           => 'marketplace',
                'current_files'       => $currentFiles,
                'max_upload_filesize' => 8192,
                'upload_url'          => 'mobile/file',
            ])
        );

        $this->addFooter()
            ->addFields(
                Builder::submit('submit', )->label(__p('core::web.save')),
                Builder::cancelButton()->sizeLarge(),
            );
    }
}
