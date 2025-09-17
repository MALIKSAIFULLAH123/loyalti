<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Photo\Models\Album;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class AddPhotosForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName photo_album.add_photos
 * @driverType form
 */
class AddPhotosForm extends AbstractForm
{
    private Album $album;

    protected bool $allowVideo = false;

    /**
     * @throws AuthenticationException
     */
    public function boot(AlbumRepositoryInterface $repository, ?int $id = null): void
    {
        $context          = user();
        $this->album      = $repository->with(['items'])->find($id);
        $this->allowVideo = $this->allowUploadVideo($context);
    }

    protected function prepare(): void
    {
        $this->title('')
            ->action(url_utility()->makeApiUrl('photo-album/upload-media'))
            ->asPost()
            ->setBackProps(__p('core::web.photos'))
            ->submitAction('submitAlbumPhotoDetail')
            ->setAttribute('isEmptyAlbum', $this->album->items->isEmpty())
            ->setValue([
                'id'         => $this->album->entityId(),
                'privacy'    => $this->album->privacy,
                'owner_id'   => $this->album->ownerId(),
                'owner_type' => $this->album->ownerType(),
                'text'       => $this->album->albumText->description,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $fields = $this->buildFields();

        if (is_array($fields)) {
            $basic->addFields(...$fields);
        }
    }

    /**
     * @return array<int, AbstractField>|null
     */
    protected function buildFields(): ?array
    {
        if ($this->album->album_type !== Album::NORMAL_ALBUM) {
            return null;
        }

        $context = user();

        if (!app('events')->dispatch('photo.album.can_upload_to_album', [$context, $this->album->owner, 'photo'], true)) {
            return null;
        }

        $types = ['photo'];
        if ($this->allowVideo) {
            $types[] = 'video';
        }

        $uploadField = Builder::simpleUploadPhotos('items')
            ->required()
            ->allowTypes($types)
            ->placeholder(__p('photo::phrase.add'))
            ->dialogTitle(__p('photo::phrase.add_photos', ['allowVideo' => (int) $this->allowVideo]));

        if ($this->album->items->isEmpty()) {
            $uploadField->sizeSmall();
        }

        return [$uploadField, Builder::hidden('id'), Builder::hidden('text')];
    }

    protected function allowUploadVideo(User $context): bool
    {
        if (!Settings::get('photo.photo_allow_uploading_video_to_photo_album', true)) {
            return false;
        }

        if (!app('events')->dispatch('photo.album.can_upload_to_album', [$context, $this->album->owner, 'video'], true)) {
            return false;
        }

        return true;
    }
}
