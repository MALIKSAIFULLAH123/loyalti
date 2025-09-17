<?php

namespace MetaFox\Music\Http\Resources\v1\Album;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Music\Http\Requests\v1\Album\CreateFormRequest;
use MetaFox\Music\Models\Album as Model;
use MetaFox\Music\Policies\AlbumPolicy;
use MetaFox\Music\Repositories\AlbumRepositoryInterface;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\Yup\Yup;
use MetaFox\Form\AbstractField;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreAlbumForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreAlbumMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait;

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

        policy_authorize(AlbumPolicy::class, 'create', $context, $owner);
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
        $privacyField = $this->buildPrivacyField()
            ->description(__p('music::phrase.control_who_can_see_this_album_and_any_songs_connected_to_it'));
        $publishedYearWarning = $this->getPublishedYearWarning();

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
                        ->max((int) Carbon::now()->addYear()->format('Y'))
                        ->unint($publishedYearWarning)
                        ->setError('typeError', $publishedYearWarning)
                ),
            $this->buildTextField(),
            Builder::singlePhoto('thumbnail')
                ->label(__p('music::phrase.cover_photo'))
                ->itemType('music_album')
                ->previewUrl($this->resource?->image_file_id ? $this->resource?->image : '')
                ->showWhen([
                    'or',
                    ['neq', 'file', null], ['truthy', 'useThumbnail'],
                ]),
            $this->buildCategoryField(),
            Builder::hidden('module_id'),
            Builder::hidden('owner_id'),
            $privacyField,
        );
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

    protected function getPublishedYearWarning(): string
    {
        return __p('music::validation.published_year_is_invalid', ['year' => 1900]);
    }

    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('music::phrase.add_some_description_to_your_album'));
        }

        return Builder::textArea('text')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('music::phrase.add_some_description_to_your_album'));
    }
}
