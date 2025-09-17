<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1\Song;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Music\Models\Song as Model;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * Class EditSelectingSongForm.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class EditSelectingSongForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('music::phrase.edit_song'));
    }

    protected function initialize(): void
    {
        $minSongNameLength = Settings::get(
            'music.music_song.minimum_name_length',
            MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH
        );
        $maxSongNameLength = Settings::get(
            'music.music_song.maximum_name_length',
            MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH
        );

        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('name')
                ->required()
                ->marginNone()
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
            $this->buildTextField()->marginDense()
        );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('description')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('music::phrase.add_some_description_to_your_song'));
        }

        return Builder::textArea('description')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('music::phrase.add_some_description_to_your_song'));
    }
}
