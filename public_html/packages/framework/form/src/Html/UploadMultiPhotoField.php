<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use MetaFox\Platform\Facades\Settings;

/**
 * Class UploadMultiPhotoField.
 * @driverName uploadMultiMedia
 */
class UploadMultiPhotoField extends File
{
    public function initialize(): void
    {
        $this->component('UploadMultiPhoto')
            ->itemType('photo')
            ->placeholder(__p('core::web.add_photos'))
            ->name('files')
            ->maxUploadFileSize(Settings::get('storage.filesystems.max_upload_filesize'))
            ->fullWidth()
            ->allowDrop(false)
            ->label(__p('photo::phrase.photos'));
    }

    public function allowEditPhoto(bool $allowed = true): self
    {
        return $this->setAttribute('allowEditPhoto', $allowed);
    }

    public function editPhotoAction(array $action): self
    {
        return $this->setAttribute('editPhotoAction', $action);
    }

    public function mappingEditPhotoFields(array $fields = ['title', 'text', 'mature', 'extra']): self
    {
        return $this->setAttribute('mappingEditPhotoFields', $fields);
    }
}
