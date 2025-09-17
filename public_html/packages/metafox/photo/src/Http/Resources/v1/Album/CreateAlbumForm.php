<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Form\Section;
use MetaFox\Photo\Http\Requests\v1\Album\CreateFormRequest;
use MetaFox\Photo\Models\Album as Model;
use MetaFox\Photo\Policies\AlbumPolicy;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateAlbumForm.
 *
 * @property Model $resource
 * @driverName photo_album.store
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateAlbumForm extends AbstractForm
{
    use PrivacyFieldTrait;

    /**
     * @var bool
     */
    protected bool $allowVideo = false;

    protected bool $isAllowedUploadItem = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(CreateFormRequest $request): void
    {
        $context = user();
        $params  = $request->validated();

        $ownerId = Arr::get($params, 'owner_id');
        $this->setOwner($context);
        if ($ownerId > 0) {
            $owner = UserEntity::getById($ownerId)->detail;
            $this->setOwner($owner);
        }

        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Model::ENTITY_TYPE,
            1,
            ['messageFormat' => 'text', 'message' => __p('photo::phrase.quota_control_photo_album_invalid')]
        );
        policy_authorize(AlbumPolicy::class, 'create', $context, $this->owner);

        $this->resource = new Model($params);

        $this->allowVideo = $this->allowUploadVideo($context);

        $this->isAllowedUploadItem = $context->hasPermissionTo('photo.create');
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();

        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'photo_album.item_privacy');

        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $this->title(__p('photo::phrase.create_new_photo_album'))
            ->action(url_utility()->makeApiUrl('photo-album'))
            ->setBackProps(__p('core::web.photos'))
            ->asPost()
            ->submitAction('@album/uploadMultiAlbumItems/submit')
            ->setValue([
                'privacy'    => $privacy,
                'owner_id'   => $this->resource->owner_id ?? 0,
                'owner_type' => $this->owner->entityType(),
                'text'       => '', // set default value to prevent dirty
                'title'      => '',
                'items'      => [],
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $minAlbumNameLength = Settings::get(
            'photo.album.minimum_name_length',
            MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH
        );
        $maxAlbumNameLength = Settings::get(
            'photo.album.maximum_name_length',
            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
        );

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->returnKeyType('next')
                ->marginNormal()
                ->label(__p('photo::phrase.album_name'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxAlbumNameLength]))
                ->minLength($minAlbumNameLength)
                ->maxLength($maxAlbumNameLength)
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->minLength(
                            $minAlbumNameLength,
                            __p(
                                'core::validation.field_minimum_length_of_characters',
                                [
                                    'number' => $minAlbumNameLength,
                                    'field'  => '${path}',
                                ]
                            )
                        )
                        ->maxLength(
                            $maxAlbumNameLength,
                            __p('core::validation.field_maximum_length_of_characters', [
                                'min'   => $minAlbumNameLength,
                                'max'   => $maxAlbumNameLength,
                                'field' => '${path}',
                            ])
                        )
                ),
        );

        $basic = $this->buildUploadField($basic);

        $basic->addFields(
            $this->buildTextField(),
            $this->buildPrivacyFieldForAlbum(),
            Builder::hidden('owner_id'),
        );

        $this->buildFooter();
    }

    /**
     * @throws AuthenticationException
     */
    protected function buildPrivacyFieldForAlbum(): AbstractField
    {
        $context = user();

        if (!$context->hasPermissionTo('photo_album.set_privacy')) {
            return Builder::hidden('privacy');
        }

        if ($this->owner instanceof HasPrivacyMember) {
            return Builder::hidden('privacy');
        }

        return $this->buildPrivacyField()
            ->label(__p('photo::phrase.album_privacy'))
            ->description(__p('photo::phrase.description_for_privacy_field'));
    }

    /**
     * @throws AuthenticationException
     */
    protected function buildUploadField(Section $basic): Section
    {
        $context = user();

        if (!app('events')->dispatch('photo.album.can_upload_to_album', [$context, $this->owner, 'photo'], true)) {
            return $basic;
        }

        $types = ['photo'];

        $isVideoAllowed = (int) $this->allowVideo;

        if ($isVideoAllowed) {
            $types[] = 'video';
        }

        $maxMediaPerUpload = $context->getPermissionValue('photo.maximum_number_of_media_per_upload');
        $maxPhotoSize      = file_type()->getFilesizePerType('photo');
        $maxVideoSize      = file_type()->getFilesizePerType('video');
        $description       = __p('photo::phrase.upload_multiple_photo_description', [
            'allowVideo'        => $isVideoAllowed,
            'maxPhotoSize'      => $maxPhotoSize,
            'maxVideoSize'      => $maxVideoSize,
            'valueVideoSize'    => file_type()->getFilesizeReadableString($maxVideoSize),
            'valuePhotoSize'    => file_type()->getFilesizeReadableString($maxPhotoSize),
            'maxMediaPerUpload' => $maxMediaPerUpload,
        ]);

        $basic->addField(
            Builder::uploadMultiAlbumItem('items')
                ->allowEditPhoto()
                ->mappingEditPhotoFields()
                ->allowTypes($types)
                ->allowAddItems($this->isAllowedUploadItem)
                ->dialogTitle(__p('photo::phrase.add_photos', ['allowVideo' => $isVideoAllowed]))
                ->description($this->isAllowedUploadItem ? $description : '')
                ->required()
        );

        return $basic;
    }

    protected function allowUploadVideo(User $context): bool
    {
        if (!Settings::get('photo.photo_allow_uploading_video_to_photo_album', true)) {
            return false;
        }

        if (!app('events')->dispatch('photo.album.can_upload_to_album', [$context, $this->owner, 'video'], true)) {
            return false;
        }

        return true;
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->required(false)
                ->returnKeyType('default')
                ->placeholder(__p('photo::phrase.add_some_content_to_your_photo_album'))
                ->label(__p('photo::phrase.album_description'));
        }

        return Builder::textArea('text')
            ->required(false)
            ->returnKeyType('default')
            ->placeholder(__p('photo::phrase.add_some_content_to_your_photo_album'))
            ->label(__p('photo::phrase.album_description'));
    }

    private function buildFooter(): void
    {
        $this->addDefaultFooter();
    }
}
