<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Story\Models\Story as Model;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Story\Support\StorySupport;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateStoryPhotoForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateStoryPhotoForm extends CreateStoryTextForm
{
    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::iconToggle('toggle')
                ->label(__p('story::phrase.create_story'))
                ->componentLabel('h1')
                ->marginNone()
                ->sxFieldWrapper([
                    'marginBottom' => 1,
                ])
                ->color('text.primary')
                ->icon('ico-gear')
                ->tooltip(__p('story::phrase.setting_privacy'))
                ->variant('h3'),
            Builder::privacy()
                ->showWhen(['eq', 'toggle', 1])
                ->options(StoryFacades::getPrivacyOptions()),
        );

        $this->buildLifespans($basic);

        $basic->addFields(
            Builder::addTextStyle()
                ->sxFieldWrapper([
                    'marginTop' => 1,
                ])
                ->setAttribute('options', StoryFacades::getFontStyleOptions()),
            Builder::text('expand_link')
                ->label(__p('story::phrase.add_link'))
                ->description(__p('story::web.add_link_description'))
                ->placeholder(__p('story::phrase.add_link'))
                ->yup(Yup::string()->nullable()->url(__p('validation.url', ['attribute' => __p('story::phrase.add_link')]))),
        );

        $this->addDefaultFooter();
    }

    protected function getDataValue(): array
    {
        return [
            'privacy'    => MetaFoxPrivacy::MEMBERS,
            'lifespan'   => StoryFacades::getLifespanDefault(),
            'toggle'     => 0,
            'font_style' => StorySupport::FONT_STYLE_DEFAULT,
        ];
    }
}
