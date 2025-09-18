<?php

namespace MetaFox\Story\Http\Resources\v1\Story;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Story\Models\Story as Model;
use MetaFox\Story\Models\StoryBackground;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;
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
 * Class CreateStoryTextForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateStoryTextForm extends AbstractForm
{
    protected const TEXT_SIZE_MIN = 12;
    protected const TEXT_SIZE_MAX = 80;

    protected function prepare(): void
    {
        $this->action('/story')
            ->asPost()
            ->setValue($this->getDataValue());
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::iconToggle('toggle')
                ->label(__p('story::phrase.create_story'))
                ->componentLabel('h1')
                ->color('text.primary')
                ->icon('ico-gear')
                ->marginNone()
                ->sxFieldWrapper([
                    'marginBottom' => 1,
                ])
                ->tooltip(__p('story::phrase.setting_privacy'))
                ->variant('h3'),
            Builder::privacy()
                ->showWhen(['eq', 'toggle', 1])
                ->options(StoryFacades::getPrivacyOptions()),
        );

        $this->buildLifespans($basic);

        $basic->addFields(
            Builder::textArea('text')
                ->label(__p('story::phrase.text'))
                ->placeholder(__p('story::phrase.text'))
                ->yup(Yup::string()->required()),
            Builder::text('expand_link')
                ->label(__p('story::phrase.add_link'))
                ->description(__p('story::web.add_link_description'))
                ->placeholder(__p('story::phrase.add_link'))
                ->yup(Yup::string()->nullable()->url(__p('validation.url', ['attribute' => __p('story::phrase.add_link')]))),
            Builder::textResize('size')
                ->label(__p('story::web.text_size'))
                ->sxFieldWrapper([
                    'marginTop' => 1,
                ])
                ->showTooltip()
                ->min(self::TEXT_SIZE_MIN)
                ->max(self::TEXT_SIZE_MAX),
            Builder::dropdown('font_style')
                ->label(__p('story::phrase.style'))
                ->options(StoryFacades::getFontStyleOptions()),
            Builder::selectBackground('background_id')
                ->marginNormal()
                ->options($this->getBackgroundOptions()),

        );

        $this->addDefaultFooter();
    }

    protected function getBackgroundOptions(): array
    {
        /**@var BackgroundSetRepositoryInterface $repository */
        $repository = resolve(BackgroundSetRepositoryInterface::class);

        $backgroundSet = $repository->getBackgroundSetActive();
        $data          = [];
        foreach ($backgroundSet->backgrounds as $background) {
            if (!$background instanceof StoryBackground) {
                continue;
            }

            if ($background->is_deleted) {
                continue;
            }

            $images = $background->images;
            if ($images == null) {
                continue;
            }

            $value  = Arr::get($images, 'origin');
            $label  = Arr::get($images, '50x50', $value);
            $data[] = [
                'id'    => $background->entityId(),
                'value' => $value,
                'label' => $label,
            ];
        }

        return $data;
    }

    protected function getDataValue(): array
    {
        return [
            'privacy'    => MetaFoxPrivacy::MEMBERS,
            'lifespan'   => StoryFacades::getLifespanDefault(),
            'toggle'     => 0,
            'font_style' => StorySupport::FONT_STYLE_DEFAULT,
            'type'       => StorySupport::STORY_TYPE_TEXT,
            'size'       => 28,
        ];
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
                ->showWhen(['eq', 'toggle', 1])
                ->label(__p('story::phrase.lifespan'))
                ->options(StoryFacades::getLifespanOptions())
        );
    }

    protected function getLifespanSettings(): array
    {
        return Settings::get('story.lifespan_options', StorySupport::LIFESPAN_VALUE_OPTIONS);
    }
}
