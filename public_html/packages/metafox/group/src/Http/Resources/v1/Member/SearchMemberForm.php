<?php

namespace MetaFox\Group\Http\Resources\v1\Member;

use Exception;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\SortScope;
use MetaFox\Group\Support\Browse\Scopes\SearchMember\ViewScope;
use MetaFox\Platform\Support\Browse\Browse;

class SearchMemberForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/group-member')
            ->noHeader()
            ->acceptPageParams(['q', 'view', 'sort', 'sort_type', 'group_id'])
            ->setValue([
                'view'      => ViewScope::VIEW_ALL,
                'sort'      => SortScope::SORT_NAME,
                'sort_type' => Browse::SORT_TYPE_ASC,
            ]);
    }

    /**
     * @throws Exception
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic()->variant('horizontal');
        $basic->addFields(
            Builder::dropdown('sort')
                ->fullWidth(false)
                ->sxFieldWrapper([
                    'width'        => [
                        'xs' => '100%',
                        'sm' => '260px',
                    ],
                    'mb'           => 1,
                    'paddingRight' => [
                        'xs' => '0 !important',
                        'sm' => '8px !important',
                    ],
                ])
                ->marginNone()
                ->sizeSmall()
                ->variant('outlined')
                ->label(__p('core::phrase.sort_label'))
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
            Builder::dropdown('sort_type')
                ->label(__p('core::phrase.sort_by'))
                ->fullWidth(false)
                ->sxFieldWrapper([
                    'width'        => [
                        'xs' => '100%',
                        'sm' => '180px',
                    ],
                    'mb'           => 1,
                    'paddingRight' => [
                        'xs' => '0 !important',
                        'sm' => '8px !important',
                    ],
                ])
                ->marginNone()
                ->variant('outlined')
                ->sizeSmall()
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
    }
}
