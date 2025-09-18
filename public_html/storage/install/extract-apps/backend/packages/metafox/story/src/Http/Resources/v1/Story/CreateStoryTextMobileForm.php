<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Story\Models\Story as Model;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateStoryTextMobileForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateStoryTextMobileForm extends CreateStoryTextForm
{
    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::privacy()
                ->options(StoryFacades::getPrivacyOptions()),
        );

        $this->buildLifespans($basic);

        $basic->addFields(
            Builder::dropdown('font_style')
                ->label(__p('story::phrase.style'))
                ->sxFieldWrapper([
                    'mt'      => 1,
                    'display' => 'none',
                ])
                ->options(StoryFacades::getFontStyleOptions()),
            Builder::selectBackground('background_id')
                ->sxFieldWrapper([
                    'mt'      => 1,
                    'display' => 'none',
                ])
                ->options($this->getBackgroundOptions()),
            Builder::text('expand_link')
                ->label(__p('story::phrase.add_link'))
                ->placeholder(__p('story::phrase.add_link'))
                ->sxFieldWrapper([
                    'mt'      => 1,
                    'display' => 'none',
                ])
                ->yup(Yup::string()->nullable()->url(__p('validation.url', ['attribute' => __p('story::phrase.add_link')]))),
        );
    }

    protected function buildLifespans(Section $section): void
    {
        $settings = $this->getLifespanSettings();

        if (count($settings) === 1) {
            $section->addFields(
                Builder::alert('_alert_lifespan_message')
                    ->asInfo()
                    ->message(__p('story::web.your_story_will_be_visible_number_hour', [
                        'number' => StoryFacades::getLifespanDefault(),
                    ])),
                Builder::hidden('lifespan')
            );

            return;
        }

        $section->addFields(
            Builder::dropdown('lifespan')
                ->label(__p('story::phrase.lifespan'))
                ->options(StoryFacades::getLifespanOptions())
        );
    }

}
