<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Support\Form\Field;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Html\File;
use MetaFox\Photo\Support\Facades\Photo;
use MetaFox\Photo\Support\Traits\MultipleTypeUploadTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

class SimpleUploadPhotosField extends File
{
    use MultipleTypeUploadTrait;

    /**
     * @throws AuthenticationException
     */
    public function initialize(): void
    {
        $context           = user();
        $maxMediaPerUpload = $context->getPermissionValue('photo.maximum_number_of_media_per_upload');

        $accept = $this->getAcceptableMimeTypes(['photo']);

        $this->component('SimpleUploadPhotos')
            ->itemType('photo')
            ->placeholder(__p('photo::phrase.add_photos', ['allowVideo' => 0]))
            ->name('files')
            ->accepts($accept)
            ->maxFiles($maxMediaPerUpload)
            ->acceptFail(__p('photo::phrase.photo_accept_type_fail'))
            ->maxUploadFileSize(Settings::get('storage.filesystems.max_upload_filesize'))
            ->isVideoUploadAllowed(false);

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

        $this->placeholder(__p('photo::phrase.add_photos', ['allowVideo' => (int) $isVideoAllow]));

        return $this;
    }

    public function dialogTitle(string $title): static
    {
        return $this->setAttribute('dialogTitle', $title);
    }
}
