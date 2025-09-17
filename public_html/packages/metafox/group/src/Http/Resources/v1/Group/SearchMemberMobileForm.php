<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Group;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\SortScope;
use MetaFox\Group\Support\Browse\Scopes\SearchMember\ViewScope;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @driverName group.searchMember
 * @driverType form
 * @preload    1
 */
class SearchMemberMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/search-group-member')
            ->acceptPageParams(['q', 'view', 'sort', 'sort_type'])
            ->setValue([
                'view' => ViewScope::VIEW_ALL,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView'])
            ->showWhen(['falsy', 'filters']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(__p('group::phrase.search_members'))
                ->className('mb2'),
            Builder::button('filters')
                ->forBottomSheetForm(),
        );

        $this->getBasicFields($basic);

        $bottomSheet = $this->addSection(['name' => 'bottomSheet']);
        $rules       = [
            'and',
            ['truthy', 'filters'],
            ['view', 'includes', [ViewScope::VIEW_ALL, ViewScope::VIEW_ADMIN, ViewScope::VIEW_MODERATOR]],
        ];

        $bottomSheet->addFields(
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->showWhen(['truthy', 'filters'])
                ->targets(['sort', 'sort_type']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->forBottomSheetForm()
                ->variant('standard-inlined')
                ->showWhen($rules)
                ->options([
                    [
                        'value' => SortScope::SORT_NAME,
                        'label' => __p('group::phrase.sort_name'),
                    ],
                    [
                        'value' => Browse::SORT_RECENT,
                        'label' => __p('group::phrase.joining_date'),
                    ],
                ]),
            Builder::choice('sort_type')
                ->label(__p('core::phrase.sort_by'))
                ->forBottomSheetForm()
                ->showWhen($rules)
                ->variant('standard-inlined')
                ->options([
                    [
                        'value' => Browse::SORT_TYPE_ASC,
                        'label' => __p('core::phrase.sort.a_to_z'),
                    ],
                    [
                        'value' => Browse::SORT_TYPE_DESC,
                        'label' => __p('core::phrase.sort.z_to_a'),
                    ],
                ]),
            Builder::submit()
                ->showWhen($rules)
                ->label(__p('core::phrase.show_results')),
        );
    }


    protected function initializeFlatten(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            $this->getClearSearchFieldsFlatten()
                ->targets(['sort', 'sort_type']),
            $this->getSearchFieldsFlatten()
                ->placeholder(__p('group::phrase.search_members'))
        );

        $this->getBasicFields($basic);
    }

    protected function getBasicFields(Section $section): void
    {
        $section->addFields(Builder::choice('sort')
            ->label(__p('core::phrase.sort_label'))
            ->autoSubmit()
            ->forBottomSheetForm()
            ->showWhen(['view', 'includes', [ViewScope::VIEW_ALL, ViewScope::VIEW_ADMIN, ViewScope::VIEW_MODERATOR]])
            ->options([
                [
                    'value' => SortScope::SORT_NAME,
                    'label' => __p('group::phrase.sort_name'),
                ],
                [
                    'value' => Browse::SORT_RECENT,
                    'label' => __p('group::phrase.joining_date'),
                ],
            ]),
            Builder::choice('sort_type')
                ->label(__p('core::phrase.sort_by'))
                ->autoSubmit()
                ->forBottomSheetForm()
                ->showWhen(['view', 'includes', [ViewScope::VIEW_ALL, ViewScope::VIEW_ADMIN, ViewScope::VIEW_MODERATOR]])
                ->options([
                    [
                        'value' => Browse::SORT_TYPE_ASC,
                        'label' => __p('core::phrase.sort.a_to_z'),
                    ],
                    [
                        'value' => Browse::SORT_TYPE_DESC,
                        'label' => __p('core::phrase.sort.z_to_a'),
                    ],
                ]),
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
                'label'    => __p('group::phrase.admins'),
                'showWhen' => [
                    'and',
                    ['truthy', 'item.profile_settings.core_view_admins'],
                ],
            ],
            [
                'value'    => ViewScope::VIEW_MODERATOR,
                'label'    => __p('group::phrase.moderators'),
                'showWhen' => [
                    'and',
                    ['truthy', 'item.profile_settings.core_view_admins'],
                ],
            ],
            [
                'value'    => ViewScope::VIEW_BLOCK,
                'label'    => __p('group::phrase.blocked'),
                'showWhen' => [
                    'or',
                    ['truthy', 'item.extra.can_manage_setting'],
                    ['truthy', 'item.is_moderator'],
                ],
            ],
            [
                'value'    => ViewScope::VIEW_MUTE,
                'label'    => __p('group::phrase.muted'),
                'showWhen' => [
                    'or',
                    ['truthy', 'item.extra.can_manage_setting'],
                    ['truthy', 'item.is_moderator'],
                ],
            ],
            [
                'value'    => ViewScope::VIEW_INVITE,
                'label'    => __p('group::phrase.invited'),
                'showWhen' => [
                    'or',
                    ['truthy', 'item.extra.can_manage_setting'],
                    ['truthy', 'item.is_moderator'],
                ],
            ],
        ];
    }
}
