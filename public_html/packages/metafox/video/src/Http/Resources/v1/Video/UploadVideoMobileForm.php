<?php

namespace MetaFox\Video\Http\Resources\v1\Video;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Photo\Repositories\AlbumRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Video\Http\Requests\v1\Video\CreateFormRequest;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Policies\VideoPolicy;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Traits\MatureFieldTrait;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreVideoMobileForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @driverType form-mobile
 * @driverName video.video.upload
 */
class UploadVideoMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;
    use MatureFieldTrait;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @driverType form-mobile
     * @driverName video.video.store
     */
    public function boot(CreateFormRequest $request, VideoRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();

        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        app('quota')->checkQuotaControlWhenCreateItem($context, Model::ENTITY_TYPE, 1, ['messageFormat' => 'text']);
        policy_authorize(VideoPolicy::class, 'create', $context);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'video.item_privacy');
        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }
        $defaultCategory = Settings::get('video.default_category');

        $this->title(__p('video::phrase.share_a_video'))
            ->action(url_utility()->makeApiUrl('video'))
            ->asPost()
            ->setValue([
                'module_id'    => 'video',
                'privacy'      => $privacy,
                'owner_id'     => $this->resource?->owner_id ?? 0,
                'useThumbnail' => false,
                'categories'   => $defaultCategory ? [$defaultCategory] : [],
            ]);
    }

    protected function initialize(): void
    {
        $minVideoNameLength = Settings::get('video.minimum_name_length', MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH);
        $maxVideoNameLength = Settings::get('video.maximum_name_length', MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH);

        $basic = $this->addBasic();
        if (!$this->isEdit()) {
            $basic->addFields(
                Builder::singleVideo('file')
                    ->required()
                    ->label(__p('video::phrase.select_video'))
                    ->description(__p('video::phrase.select_video_field_description'))
                    ->yup(
                        Yup::object()->required(__p('video::validation.video_file_is_a_required_field'))
                    ),
            );
        }
        $basic->addFields(
            Builder::text('title')
                ->required()
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('video::phrase.fill_in_a_title_for_your_video'))
                ->maxLength($maxVideoNameLength)
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxVideoNameLength]))
                ->yup(
                    Yup::string()->required(__p('validation.this_field_is_a_required_field'))
                        ->minLength($minVideoNameLength)
                        ->maxLength($maxVideoNameLength)
                ),
            Builder::singlePhoto('thumbnail')
                ->required(false)
                ->itemType('photo')
                ->previewUrl($this->resource?->thumbnail_file_id ? $this->resource?->image : '')
                ->showWhen([
                    'or',
                    ['neq', 'file', null],
                    ['truthy', 'useThumbnail'],
                ])
                ->label(__p('video::phrase.video_thumbnail'))
                ->placeholder(__p('video::phrase.video_thumbnail_placeholder')),
            Builder::textArea('text')
                ->required(false)
                ->returnKeyType('default')
                ->asMultiLine()
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('video::phrase.add_some_content_to_your_video')),
        );

        $this->addAlbumField($basic);

        $basic->addFields(
            $this->buildCategoryField(),
            $this->buildMobileMatureField(),
            $this->buildPrivacyFieldForVideo()
        );

        $basic->addFields(
            Builder::hidden('module_id')
                ->setValue('video'),
            Builder::hidden('owner_id'),
        );

        if (!$this->isEdit()) {
            $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.upload'))
            );
        }
    }

    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }

    protected function buildCategoryField(): AbstractField
    {
        $field = Builder::category('categories')
            ->multiple(true)
            ->sizeLarge()
            ->fullWidth()
            ->setRepository(CategoryRepositoryInterface::class);

        if ($this->isEdit()) {
            $field->setSelectedCategories($this->resource->categories);
        }

        return $field;
    }

    protected function buildPrivacyFieldForVideo(): AbstractField
    {
        $defaultAlbums = app('events')->dispatch('photo.album.get_default', [$this->resource->ownerId()], true);
        $albumId       = $this->resource->album_id;

        if ($this->isEdit()) {
            if (in_array($albumId, $defaultAlbums->pluck('id')->toArray())) {
                return Builder::hidden('privacy');
            }

            if ($albumId != 0) {
                return Builder::hidden('privacy');
            }
        }

        return $this->buildPrivacyField()
            ->description(__p('video::phrase.control_who_can_see_this_video'))
            ->fullWidth(false)
            ->minWidth(275);
    }

    protected function addAlbumField(Section $basic): void
    {
        if ($this->canUpdateAlbum()) {
            $basic->addField(
                Builder::album('album')
                    ->multiple(false)
                    ->sizeLarge()
                    ->fullWidth()
                    ->label(__p('video::phrase.video_album'))
                    ->setOwner($this->resource->owner)
                    ->setUser(user())
                    ->setRepository(AlbumRepositoryInterface::class)
                    ->yup(
                        Yup::number()
                            ->nullable()
                    )
            );

            return;
        }

        $basic->addField(
            Builder::hidden('album')
        );
    }

    protected function canUpdateAlbum(): bool
    {
        return false;
    }
}
