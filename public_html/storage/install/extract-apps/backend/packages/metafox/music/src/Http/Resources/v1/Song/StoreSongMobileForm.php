<?php

namespace MetaFox\Music\Http\Resources\v1\Song;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\PrivacyFieldMobileTrait;
use MetaFox\Music\Http\Requests\v1\Song\CreateFormRequest;
use MetaFox\Music\Models\Album;
use MetaFox\Music\Models\Song as Model;
use MetaFox\Music\Policies\SongPolicy;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Music\Repositories\SongRepositoryInterface;
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
 * Class StoreSongMobileForm.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreSongMobileForm extends AbstractForm
{
    use PrivacyFieldMobileTrait {
        PrivacyFieldMobileTrait::buildPrivacyField as buildPrivacyFieldTrait;
    }

    public bool $preserveKeys = true;

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, SongRepositoryInterface $repository, ?int $id = null): void
    {
        $context = user();
        $params  = $request->validated();
        $this->setOwner($context);
        if ($params['owner_id'] != 0) {
            $userEntity = UserEntity::getById($params['owner_id']);
            $this->setOwner($userEntity->detail);
        }

        policy_authorize(SongPolicy::class, 'create', $context, $this->owner);
        $this->resource = new Model($params);
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $context = user();
        $privacy = UserPrivacy::getItemPrivacySetting($context->entityId(), 'music_song.item_privacy');

        if ($privacy === false) {
            $privacy = MetaFoxPrivacy::EVERYONE;
        }

        $defaultGenre = Settings::get('music.music_song.song_default_genre');

        $this->title(__p('music::phrase.add_new_music'))
            ->action(url_utility()->makeApiUrl('music/song'))
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
        $basic             = $this->addBasic();
        $minSongNameLength = Settings::get(
            'music.music_song.minimum_name_length',
            MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH
        );
        $maxSongNameLength = Settings::get(
            'music.music_song.maximum_name_length',
            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
        );
        $privacyField = $this->buildPrivacyField();

        $basic->addFields(
            Builder::text('name')
                ->required()
                ->marginNormal()
                ->label(__p('music::phrase.song_title'))
                ->placeholder(__p('music::phrase.fill_in_a_name_for_your_song'))
                ->description(__p('core::phrase.maximum_length_of_characters', ['length' => $maxSongNameLength]))
                ->maxLength($maxSongNameLength)
                ->yup(
                    Yup::string()
                        ->required()
                        ->minLength(
                            $minSongNameLength,
                            __p(
                                'core::validation.title_minimum_length_of_characters',
                                ['number' => $minSongNameLength]
                            )
                        )
                        ->maxLength(
                            $maxSongNameLength,
                            __p('core::validation.title_maximum_length_of_characters', [
                                'min' => $minSongNameLength,
                                'max' => $maxSongNameLength,
                            ])
                        )
                ),
            Builder::textArea('description')
                ->required(false)
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('music::phrase.add_some_description_to_your_song')),
            $this->buildThumbnailField(),
            $this->buildCategoryField(),
            Builder::hidden('module_id'),
            Builder::hidden('owner_id'),
            $privacyField,
        );
    }

    protected function buildThumbnailField(): ?AbstractField
    {
        if ($this->checkBelongToAlbum()) {
            return null;
        }

        return Builder::singlePhoto('thumbnail')
            ->label(__p('music::phrase.cover_photo'))
            ->required(false)
            ->itemType('photo')
            ->placeholder(__p('music::phrase.add_photo'))
            ->previewUrl($this->resource?->image_file_id ? $this->resource?->image : '')
            ->showWhen([
                'or',
                ['neq', 'file', null], ['truthy', 'useThumbnail'],
            ]);
    }

    protected function buildPrivacyField(): ?AbstractField
    {
        if ($this->checkBelongToAlbum()) {
            return null;
        }

        return $this->buildPrivacyFieldTrait()
            ->description(__p('music::phrase.control_who_can_see_this_song'));
    }

    private function checkBelongToAlbum(): bool
    {
        if ($this->resource?->album instanceof Album) {
            return true;
        }

        return false;
    }

    protected function buildCategoryField(): AbstractField
    {
        $field =  Builder::category('genres')
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
    protected function isEdit(): bool
    {
        return $this->resource && $this->resource->entityId();
    }
}
