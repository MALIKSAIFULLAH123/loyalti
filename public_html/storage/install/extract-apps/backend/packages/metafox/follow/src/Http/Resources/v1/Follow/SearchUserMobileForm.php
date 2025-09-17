<?php

namespace MetaFox\Follow\Http\Resources\v1\Follow;

use MetaFox\Follow\Support\Browse\Scopes\ViewScope;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;

class SearchUserMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/follow')
            ->acceptPageParams(['q', 'view', 'user_id'])
            ->setValue([
                'view' => ViewScope::VIEW_FOLLOWER,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(__p('user::phrase.search_users'))
                ->className('mb2'),
        );

        $this->getBasicFields($basic);
    }


    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('user::phrase.search_users'))
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $viewSection = $this->addSection(['name' => 'viewSection', 'paddingBottom' => 'none'])
            ->showWhen(['falsy', 'filters']);

        $viewSection->addFields(
            Builder::choice('view')
                ->forBottomSheetForm('SFTabSelect')
                ->autoSubmit()
                ->label(__p('core::phrase.view'))
                ->options($this->handleViewOptions()),
            Builder::hidden('user_id')
        );
    }

    protected function handleViewOptions(): array
    {
        return [
            [
                'value'    => ViewScope::VIEW_FOLLOWER,
                'label'    => __p('follow::web.follower'),
                'showWhen' => [
                    'and',
                    ['truthy', 'item.profile_settings.follow_view_following'],
                ],
            ],
            [
                'value'    => ViewScope::VIEW_FOLLOWING,
                'label'    => __p('follow::web.following'),
                'showWhen' => [
                    'and',
                    ['truthy', 'item.profile_settings.follow_view_following'],
                ],
            ],
        ];
    }
}
