<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Group;

use Illuminate\Support\Facades\Auth;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

/**
 * @preload 1
 */
class SearchGroupForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/group/search')
            ->acceptPageParams($this->handleAcceptParams())
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('group::phrase.search_groups'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['category_id', 'q', 'view']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->margin('normal')
                ->options([
                    ['label' => __p('core::phrase.sort.latest'), 'value' => 'latest'], ['label' => __p('core::phrase.sort.most_joined'), 'value' => 'most_member'],
                ]),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->margin('normal')
                ->options([
                    [
                        'label' => __p('core::phrase.when.all'),
                        'value' => 'all',
                    ], [
                        'label' => __p('core::phrase.when.this_month'),
                        'value' => 'this_month',
                    ], [
                        'label' => __p('core::phrase.when.this_week'),
                        'value' => 'this_week',
                    ], [
                        'label' => __p('core::phrase.when.today'),
                        'value' => 'today',
                    ],
                ]),
        );

        CustomFieldFacade::loadFieldSearch($basic, [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'resolution'   => MetaFoxConstant::RESOLUTION_WEB,
        ]);

        $basic->addFields(
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
            Builder::filterCategory('category_id')
                ->label(__p('core::phrase.categories'))
                ->apiUrl('/group/category'),
        );
    }

    /**
     * @return string[]
     */
    protected function handleAcceptParams(): array
    {
        $result = ['q', 'when', 'sort', 'category_id', 'is_featured', 'returnUrl'];
        $fields = CustomFieldFacade::loadFieldName(Auth::user(), [
            'section_type' => CustomField::SECTION_TYPE_GROUP,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        return array_merge($result, $fields);
    }
}
