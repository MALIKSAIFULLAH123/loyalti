<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Photo\Support\Traits\MultipleTypeUploadTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Shape;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateAlbumForm.
 * @property Model $resource
 *
 * @driverType form-mobile
 * @driverName photo.album.add_items
 */
class AddItemsAlbumMobileForm extends AbstractForm
{
    use MultipleTypeUploadTrait;

    protected bool $allowVideo = false;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(AlbumRepositoryInterface $repository, ?int $id = null): void
    {
        $context          = user();
        $this->resource   = $repository->find($id);
        $this->allowVideo = $this->allowUploadVideo($context);
    }

    protected function prepare(): void
    {
        $this->title(__p('photo::phrase.add_photos', ['allowVideo' => $this->allowVideo]))
            ->action('photo-album/upload-media')
            ->asPost()
            ->setValue([
                'id'         => $this->resource->entityId(),
                'owner_id'   => $this->resource->ownerId(),
                'owner_type' => $this->resource->ownerType(),
                'items'      => [],
            ]);
    }

    protected function initialize(): void
    {
        $context           = user();
        $maxMediaPerUpload = $context->getPermissionValue('photo.maximum_number_of_media_per_upload');
        $maxPhotoSize      = file_type()->getFilesizePerType('photo');
        $maxVideoSize      = file_type()->getFilesizePerType('video');
        $acceptTypes       = ['photo'];
        $isVideoAllowed    = (int) $this->allowVideo;

        if ($this->allowVideo) {
            $acceptTypes[] = 'video';
        }

        $accept = $this->getAcceptableMimeTypes($acceptTypes);

        $basic             = $this->addBasic();
        $description       = __p('photo::phrase.upload_multiple_photo_description', [
            'allowVideo'        => $isVideoAllowed,
            'maxPhotoSize'      => $maxPhotoSize,
            'maxVideoSize'      => $maxVideoSize,
            'valueVideoSize'    => file_type()->getFilesizeReadableString($maxVideoSize),
            'valuePhotoSize'    => file_type()->getFilesizeReadableString($maxPhotoSize),
            'maxMediaPerUpload' => $maxMediaPerUpload,
        ]);

        $basic->addFields(
            Builder::multiFile('items')
                ->required()
                ->isVideoUploadAllowed($this->allowVideo)
                ->allowEditPhoto()
                ->itemType('photo')
                ->accept($accept)
                ->acceptFail(__p('photo::phrase.photo_accept_type_fail'))
                ->label(__p('photo::phrase.add_photos', ['allowVideo' => $this->allowVideo]))
                ->placeholder(__p('photo::phrase.upload_multiple_photo_placeholder', ['allowVideo' => $this->allowVideo]))
                ->description($description)
                ->yup(
                    $this->itemUploadValidator()
                ),
            Builder::hidden('id'),
            Builder::hidden('owner_id'),
            Builder::hidden('owner_type'),
        );
    }

    protected function allowUploadVideo(User $context): bool
    {
        if (!Settings::get('photo.photo_allow_uploading_video_to_photo_album', true)) {
            return false;
        }

        if (!app('events')->dispatch('photo.album.can_upload_to_album', [$context, $this->resource->owner, 'video'], true)) {
            return false;
        }

        return true;
    }

    protected function itemUploadValidator(): Shape
    {
        $context           = user();
        $maxMediaPerUpload = $context->getPermissionValue('photo.maximum_number_of_media_per_upload');

        $validator = Yup::array()
            ->required(__p('photo::validation.media_files_are_required'))
            ->min(1, __p('photo::validation.media_files_are_required'));

        if ($maxMediaPerUpload) {
            $validator->max($maxMediaPerUpload, __p('photo::phrase.maximum_per_upload_limit_reached', [
                'limit' => (int) $maxMediaPerUpload,
            ]));
        }

        return $validator;
    }
}
