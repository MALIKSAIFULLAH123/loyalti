<?php

namespace MetaFox\Music\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Music\Repositories\GenreRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'music';
        $vars   = [
            'music.music_song.minimum_name_length',
            'music.music_song.maximum_name_length',
            'music.music_album.minimum_name_length',
            'music.music_album.maximum_name_length',
            'music.music_playlist.minimum_name_length',
            'music.music_playlist.maximum_name_length',
            'music.music_song.song_default_genre',
            'music.music_song.auto_play',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic             = $this->addBasic();
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;

        $basic->addFields(
            Builder::text('music.music_song.minimum_name_length')
                ->label(__p('music::phrase.minimum_song_name_length'))
                ->yup(
                    Yup::number()->required()->int()->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('music.music_song.maximum_name_length')
                ->label(__p('music::phrase.maximum_song_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()->required()->int()
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(Yup::number()->min(['ref' => 'minimum_name_length']))
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('music.music_album.minimum_name_length')
                ->label(__p('music::phrase.minimum_album_name_length'))
                ->yup(
                    Yup::number()->required()->int()->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('music.music_album.maximum_name_length')
                ->label(__p('music::phrase.maximum_album_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()->required()->int()
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(Yup::number()->min(['ref' => 'minimum_name_length']))
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('music.music_playlist.minimum_name_length')
                ->label(__p('music::phrase.minimum_playlist_name_length'))
                ->yup(
                    Yup::number()->required()->int()->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('music.music_playlist.maximum_name_length')
                ->label(__p('music::phrase.maximum_playlist_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()->required()->int()
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(Yup::number()->min(['ref' => 'minimum_name_length']))
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::choice('music.music_song.song_default_genre')
                ->label(__p('music::phrase.music_default_genre'))
                ->description(__p('music::phrase.music_default_genre_description'))
                ->disableClearable()
                ->required()
                ->options($this->getGenreOptions()),
            Builder::switch('music.music_song.auto_play')
                ->label(__p('music::phrase.autoplay_when_viewing_song_details_label'))
                ->description(__p('music::phrase.autoplay_when_viewing_song_details_desc')),
        );

        $this->addDefaultFooter(true);
    }

    private function getGenreOptions(): array
    {
        return resolve(GenreRepositoryInterface::class)->getCategoriesForForm();
    }
}
