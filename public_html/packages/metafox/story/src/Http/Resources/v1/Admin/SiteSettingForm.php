<?php

namespace MetaFox\Story\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Story\Support\StorySupport;
use MetaFox\Yup\Yup;


/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub
 */

/**
 * Class SiteSettingForm.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'story';
        $vars   = [
            'story.home_page_style',
            'story.video_service',
            'story.only_friends',
            'story.duration_video_story',
            'story.lifespan_options',
            'story.lifespan_default',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/' . $module)
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()->addFields(
            Builder::choice('story.video_service')
                ->required()
                ->label(__p('story::phrase.story_services'))
                ->description(__p('story::phrase.story_service_description'))
                ->multiple(false)
                ->options($this->getServiceOptions()),
            Builder::dropdown('story.home_page_style')
                ->label(__p('story::admin.home_page_style'))
                ->description(__p('story::admin.home_page_style_desc'))
                ->options([
                    [
                        'value' => StorySupport::DISPLAY_THE_USER_AVATAR,
                        'label' => __p('story::admin.display_the_user_avatar_only'),
                    ],
                    [
                        'value' => StorySupport::DISPLAY_THE_THUMBNAIL_AND_AVATAR,
                        'label' => __p('story::admin.display_the_story_thumbnail_with_the_user_avatar'),
                    ],
                ]),
            Builder::dropdown('story.duration_video_story')
                ->label(__p('story::admin.duration_video_story_label'))
                ->description(__p('story::admin.duration_video_story_desc'))
                ->options(StoryFacades::getVideoDurationOptions()),
            Builder::choice('story.lifespan_options')
                ->label(__p('story::admin.lifespan_options_for_stories_label'))
                ->multiple()
                ->required()
                ->description(__p('story::admin.lifespan_options_for_stories_desc'))
                ->options($this->getLifespanOptions())
                ->yup(Yup::array()
                    ->min(1, __p('validation.min.array', [
                        'attribute' => __p('story::admin.lifespan_options_for_stories_label'),
                        'min'       => 1,
                    ]))),
            Builder::choice('story.lifespan_default')
                ->label(__p('story::admin.lifespan_default_for_stories_label'))
                ->description(__p('story::admin.lifespan_default_for_stories_desc'))
                ->required()
                ->options($this->getLifespanOptions())
                ->yup(Yup::string()->required()),
            Builder::switch('story.only_friends')
                ->label(__p('story::admin.friends_only_label'))
                ->description(__p('story::admin.friends_only_description'))
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @return array<int, mixed>
     */
    protected function getServiceOptions(): array
    {
        return StoryFacades::getServicesOptions();
    }

    public function getLifespanOptions(): array
    {
        $options = [];
        foreach (StorySupport::LIFESPAN_VALUE_OPTIONS as $value) {
            $options[] = [
                'value' => $value,
                'label' => __p('story::phrase.lifespan_number_hour', ['number' => $value]),
            ];
        }

        return $options;
    }

    /**
     * validated.
     *
     * @param Request $request
     *
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $data = $request->all();

        return $request->validate([
            'story.lifespan_options'     => ['array', 'min:1'],
            'story.lifespan_default'     => ['sometimes', new AllowInRule(
                Arr::get($data, 'story.lifespan_options'),
                __p('validation.in_array', [
                    'attribute' => __p('story::admin.lifespan_default_for_stories_label'),
                    'other'     => __p('story::admin.lifespan_options_for_stories_label'),
                ])
            )],
            'story.home_page_style'      => ['sometimes'],
            'story.video_service'        => ['sometimes'],
            'story.only_friends'         => ['sometimes'],
            'story.duration_video_story' => ['sometimes'],
        ], [
            'story.lifespan_options' => __p('validation.min.array', [
                'attribute' => __p('story::admin.lifespan_options_for_stories_label'),
                'min'       => 1,
            ]),
        ]);
    }

}
