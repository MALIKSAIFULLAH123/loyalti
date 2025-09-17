<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Support\Form\Field;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Html\UploadMultiPhotoField;
use MetaFox\Photo\Support\Traits\MultipleTypeUploadTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

class UploadMultiAlbumItemField extends UploadMultiPhotoField
{
    use MultipleTypeUploadTrait;

    /**
     * @throws AuthenticationException
     */
    public function initialize(): void
    {
        $context           = user();
        $maxMediaPerUpload = $context->getPermissionValue('photo.maximum_number_of_media_per_upload');
        $maxPhotoSize      = file_type()->getFilesizePerType('photo');
        $maxVideoSize      = file_type()->getFilesizePerType('video');
        $accept            = $this->getAcceptableMimeTypes(['photo']);

        $this->component('UploadMultiAlbumItem')
            ->itemType('photo')
            ->label(__p('photo::phrase.add_photos_label', ['allowVideo' => 0]))
            ->name('files')
            ->placeholder(__p(
                'photo::phrase.upload_multiple_photo_placeholder',
                ['allowVideo' => 0]
            ))
            ->description(__p(
                'photo::phrase.upload_multiple_photo_description',
                [
                    'allowVideo'        => 0,
                    'maxPhotoSize'      => $maxPhotoSize,
                    'maxVideoSize'      => $maxVideoSize,
                    'valueVideoSize'    => file_type()->getFilesizeReadableString($maxVideoSize),
                    'valuePhotoSize'    => file_type()->getFilesizeReadableString($maxPhotoSize),
                    'maxMediaPerUpload' => $maxMediaPerUpload,
                ]
            ))
            ->accepts($accept)
            ->maxUploadFileSize(Settings::get('storage.filesystems.max_upload_filesize'))
            ->acceptFail(__p('photo::phrase.photo_accept_type_fail'))
            ->isVideoUploadAllowed(false)
            ->setAttribute('allowAddItems', true);

        $validator = Yup::array()
            ->of(
                Yup::object()
                    ->addProperty('id', Yup::number())
                    ->addProperty('type', Yup::string())
                    ->addProperty('status', Yup::string())
            );

        if ($maxMediaPerUpload) {
            $validator->maxWhen([
                'value' => (int) $maxMediaPerUpload,
                'when'  => ['truthy', 'item.uid'],
            ], __p('photo::phrase.maximum_per_upload_limit_reached', [
                'limit' => (int) $maxMediaPerUpload,
            ]));
        }

        $this->yup($validator);
    }

    /**
     * @param  array<string> $types
     * @return $this
     */
    public function allowTypes(array $types): self
    {
        $accept = $this->getAcceptableMimeTypes($types);

        $isVideoAllow = in_array('video', $types);

        $this->accepts($accept);

        $this->isVideoUploadAllowed($isVideoAllow);

        $this->label(__p('photo::phrase.add_photos_label', ['allowVideo' => (int) $isVideoAllow]));

        $this->placeholder(__p(
            'photo::phrase.upload_multiple_photo_placeholder',
            ['allowVideo' => (int) $isVideoAllow]
        ));

        return $this;
    }

    public function dialogTitle(string $title): static
    {
        return $this->setAttribute('dialogTitle', $title);
    }

    public function allowAddItems(bool $flag = true): self
    {
        return $this->setAttribute('allowAddItems', $flag);
    }

    public function allowRemoveItems(bool $flag = true): self
    {
        return $this->setAttribute('allowRemoveItems', $flag);
    }
}
