<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\PrivacyFieldTrait;
use MetaFox\Music\Http\Requests\v1\Album\CreateFormRequest;
use MetaFox\Music\Models\Album as Model;
use MetaFox\Music\Models\Song;
use MetaFox\Music\Policies\AlbumPolicy;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\ArrayShape;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreAlbumForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreAlbumForm extends AbstractForm
{
    use PrivacyFieldTrait;

    public bool $preserveKeys = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, AlbumRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();
        $this->setOwner($context);
        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Song::ENTITY_TYPE,
            1,
            [
                'message'       => __p('music::web.you_have_reached_your_limit', ['entity_type' => Song::ENTITY_TYPE]),
                'messageFormat' => 'text',
            ]
        );

        app('quota')->checkQuotaControlWhenCreateItem(
            $context,
            Model::ENTITY_TYPE,
            1,
            [
                'message'       => __p('music::web.you_have_reached_your_limit', ['entity_type' => Model::ENTITY_TYPE]),
                'messageFormat' => 'text',
            ]
        );
        policy_authorize(AlbumPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'music_album.item_privacy');

        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $defaultGenre = Settings::get('music.music_song.song_default_genre');

        $this->title(__p('music::phrase.add_new_album'))
            ->action(url_utility()->makeApiUrl('music/album'))
            ->asPost()
            ->setBackProps(__p('core::web.music'))
            ->setValue([
                'module_id'    => 'music',
                'privacy'      => $privacy,
                'useThumbnail' => true,
                'owner_id'     => $this->resource->owner_id,
                'genres'       => [$defaultGenre],
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic              = $this->addBasic();
        $minAlbumNameLength = Settings::get(
            'music.music_album.minimum_name_length',
            MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH
        );
        $maxAlbumNameLength = Settings::get(
            'music.music_album.maximum_name_length',
            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
        );

        $privacyField         = $this->buildPrivacyField()->description(__p('music::phrase.control_who_can_see_this_album_and_any_songs_connected_to_it'));
        $publishedYearWarning = $this->getPublishedYearWarning();
        $currentYear          = (int) Carbon::now()->format('Y');

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->marginNormal()
                ->label(__p('music::phrase.album_title'))
                ->placeholder(__p('music::phrase.fill_in_a_name_for_your_album'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxAlbumNameLength]))
                ->maxLength($maxAlbumNameLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(
                            $minAlbumNameLength,
                            __p(
                                'core::validation.title_minimum_length_of_characters',
                                ['number' => $minAlbumNameLength]
                            )
                        )
                        ->maxLength(
                            $maxAlbumNameLength,
                            __p('core::validation.title_maximum_length_of_characters', [
                                'min' => $minAlbumNameLength,
                                'max' => $maxAlbumNameLength,
                            ])
                        )
                ),
            Builder::text('year')
                ->label(__p('music::phrase.published_year'))
                ->required()
                ->minLength(4)
                ->maxLength(4)
                ->yup(
                    Yup::number()
                        ->required(__p('music::validation.published_year_is_a_required_field'))
                        ->min(1900, $publishedYearWarning)
                        ->max($currentYear, __p('music::validation.the_year_must_be_less_than_or_equal_to_number', ['number' => $currentYear]))
                        ->unint($publishedYearWarning)
                        ->setError('typeError', $publishedYearWarning)
                ),
            $this->handleMusicField(),
            $this->buildTextField(),
            Builder::singlePhoto('thumbnail')
                ->aspectRatio('1:1')
                ->widthPhoto('160px')
                ->required(false)
                ->itemType('music_album')
                ->label(__p('music::phrase.cover_photo'))
                ->previewUrl($this->resource?->image_file_id ? $this->resource?->image : '')
                ->showWhen([
                    'or',
                    ['neq', 'file', null], ['truthy', 'useThumbnail'],
                ]),
            Builder::attachment()
                ->itemType('music_album'),
            $this->buildCategoryField(),
            Builder::hidden('module_id'),
            Builder::hidden('owner_id'),
            $privacyField,
        );

        $submitLabel = __p('core::phrase.upload');

        if ($this->isEdit()) {
            $submitLabel = __p('core::phrase.save_changes');
        }

        $this->addFooter()
            ->addFields(
                Builder::submit('__submit')->label($submitLabel),
                Builder::cancelButton()->sizeMedium(),
            );

        // force returnUrl as string
        $basic->addField(
            Builder::hidden('returnUrl')
        );
    }

    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }

    protected function getPublishedYearWarning(): string
    {
        return __p('music::validation.published_year_is_invalid', ['year' => 1900]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function validatedSongs(): ArrayShape
    {
        $maxSongPerUpload  = user()->getPermissionValue('music_song.maximum_number_of_songs_per_upload');
        $minSongNameLength = Settings::get(
            'music.music_song.minimum_name_length',
            MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH
        );
        $maxSongNameLength = Settings::get(
            'music.music_song.maximum_name_length',
            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
        );

        $yup = Yup::array()
            ->required(__p('music::validation.file_is_a_required_field'))
            ->minWhen([
                'value' => 1,
                'when'  => ['neq', 'item.status', MetaFoxConstant::FILE_REMOVE_STATUS],
            ], __p('core::validation.this_field_is_a_required_field'))
            ->of(
                Yup::object()
                    ->addProperty('name', Yup::string()
                        ->required(__p('core::validation.this_field_is_a_required_field'))
                        ->minLength(
                            $minSongNameLength,
                            __p('core::validation.field_minimum_length_of_characters', [
                                'number' => $minSongNameLength,
                                'field'  => __p('core::phrase.name'),
                            ])
                        )
                        ->maxLength(
                            $maxSongNameLength,
                            __p('core::validation.field_maximum_length_of_characters', [
                                'min'   => $minSongNameLength,
                                'max'   => $maxSongNameLength,
                                'field' => __p('core::phrase.name'),
                            ])
                        ))
                    ->addProperty('description', Yup::string())
                    ->addProperty('id', Yup::number())
            );

        if ($maxSongPerUpload > 0) {
            $yup->maxWhen([
                'value' => $maxSongPerUpload,
                'when'  => ['eq', 'item.status', MetaFoxConstant::FILE_NEW_STATUS],
            ], __p('music::phrase.maximum_per_upload_limit_reached', [
                'limit' => (int) $maxSongPerUpload,
            ]));
        }

        return $yup;
    }

    protected function handleMusicField(): AbstractField
    {
        $context           = user();
        $maxSongSize       = file_type()->getFilesizePerType('music');
        $maxSongPerUpload  = $context->getPermissionValue('music_song.maximum_number_of_songs_per_upload');
        $maxSongNameLength = Settings::get(
            'music.music_song.maximum_name_length',
            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
        );

        $builder = Builder::multiAudio('songs')
            ->required()
            ->itemType('music_song')
            ->label(__p('music::phrase.upload'))
            ->placeholder(__p('music::phrase.select_music'))
            ->description(__p('music::phrase.select_music_field_description', [
                'maxSongSize'      => file_type()->getFilesizeReadableString($maxSongSize),
                'maxSongPerUpload' => $maxSongPerUpload,
            ]))
            ->maxUploadFileSize(Settings::get('storage.filesystems.max_upload_filesize'))
            ->storageId('music')
            ->setAttribute('max_length_name', $maxSongNameLength)
            ->setAttributes([
                'appName'      => 'music',
                'resourceName' => 'music_song',
                'formEditItem' => 'edit_selecting',
            ])
            ->yup($this->validatedSongs());

        $accepts = $builder->getAttribute('accept');

        if ($accepts) {
            $builder->setAttribute('messageAcceptFail', __p('music::phrase.please_select_audio_files_in_format', ['format' => $accepts]));
        }

        if ($maxSongPerUpload > 0) {
            $builder->maxFilesPerUpload($maxSongPerUpload);
        }

        return $builder;
    }

    protected function buildCategoryField(): AbstractField
    {
        $field = Builder::category('genres')
            ->required()
            ->multiple(true)
            ->label(__p('music::phrase.genres'))
            ->sizeLarge()
            ->setRepository(GenreRepositoryInterface::class)
            ->yup(
                Yup::array()
                    ->min(1, __p('music::validation.genres_is_a_required_field'))
            );

        if ($this->isEdit()) {
            $field->setSelectedCategories($this->resource->genres);
        }

        return $field;
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->label(__p('music::phrase.album_description'))
                ->placeholder(__p('music::phrase.add_some_description_to_your_album'));
        }

        return Builder::textArea('text')
            ->label(__p('music::phrase.album_description'))
            ->placeholder(__p('music::phrase.add_some_description_to_your_album'));
    }
}
