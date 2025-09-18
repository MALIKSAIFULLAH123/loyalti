<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Page\Support\Browse\Scopes\SearchMember\ViewScope;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName page.searchMember
 * @driverType form
 * @preload    1
 */
class SearchMemberMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('search-page-member')
            ->acceptPageParams(['q', 'view'])
            ->setValue([
                'view' => ViewScope::VIEW_ALL,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView', 'paddingBottom' => 'none']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(__p('page::phrase.search_members'))
                ->className('mb2'),
        );

        $viewSection = $this->addSection(['name' => 'viewSection', 'paddingBottom' => 'none'])
            ->showWhen(['falsy', 'filters']);

        $viewSection->addField(
            Builder::choice('view')
                ->forBottomSheetForm('SFTabSelect')
                ->autoSubmit()
                ->label(__p('core::phrase.view'))
                ->options($this->handleViewOptions()),
        );
    }

    protected function handleViewOptions(): array
    {
        return [
            [
                'value' => ViewScope::VIEW_ALL,
                'label' => __p('core::phrase.all'),
            ],
            [
                'value'    => ViewScope::VIEW_ADMIN,
                'label'    => __p('page::phrase.admins'),
                'showWhen' => [
                    'and',
                    ['truthy', 'item.profile_settings.core_view_admins'],
                ],
            ],
            [
                'value' => ViewScope::VIEW_FRIEND,
                'label' => __p('page::phrase.friends'),
            ],
            [
                'value'    => ViewScope::VIEW_BLOCK,
                'label'    => __p('page::phrase.blocked'),
                'showWhen' => [
                    'or',
                    ['truthy', 'item.is_owner'],
                    ['truthy', 'item.is_admin'],
                ],
            ],
            [
                'value'    => ViewScope::VIEW_INVITE,
                'label'    => __p('page::phrase.invited'),
                'showWhen' => [
                    'or',
                    ['truthy', 'item.is_owner'],
                    ['truthy', 'item.is_admin'],
                ],
            ],
        ];
    }
}
