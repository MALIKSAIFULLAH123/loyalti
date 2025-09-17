<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1\Page;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Profile\Support\CustomField;
use MetaFox\Profile\Support\Facade\CustomField as CustomFieldFacade;

class SearchPageForm extends AbstractForm
{
    /**
     * @return void
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $this->action('/page/search')
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
                ->placeholder(__p('page::phrase.search_pages'))
                ->className('mb2'),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['category_id', 'q', 'view']),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->margin('normal')
                ->options($this->getSortOptions()),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->margin('normal')
                ->options([
                    [
                        'label' => __p('core::phrase.when.all'),
                        'value' => 'all',
                    ],
                    [
                        'label' => __p('core::phrase.when.this_month'),
                        'value' => 'this_month',
                    ],
                    [
                        'label' => __p('core::phrase.when.this_week'),
                        'value' => 'this_week',
                    ],
                    [
                        'label' => __p('core::phrase.when.today'),
                        'value' => 'today',
                    ],
                ]),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
        );

        $custom = $this->addSection('custom');
        CustomFieldFacade::loadFieldSearch($custom, [
            'section_type' => CustomField::SECTION_TYPE_PAGE,
            'resolution'   => MetaFoxConstant::RESOLUTION_WEB,
        ]);

        $category = $this->addSection('category');
        $category->addField(
            Builder::filterCategory('category_id')
                ->label(__p('core::phrase.categories'))
                ->apiUrl('/page/category')
        );
    }

    protected function getSortOptions(): array
    {
        return [
            ['label' => __p('core::phrase.sort.recent'), 'value' => 'recent'],
            ['label' => __p('core::phrase.sort.most_liked'), 'value' => 'most_member'],
        ];
    }

    /**
     * @return string[]
     * @throws AuthenticationException
     */
    protected function handleAcceptParams(): array
    {
        $result = ['q', 'sort', 'from', 'related_comment_friend_only', 'is_featured', 'category_id', 'returnUrl', 'when'];
        $fields = CustomFieldFacade::loadFieldName(Auth::user(), [
            'section_type' => CustomField::SECTION_TYPE_PAGE,
            'view'         => CustomField::VIEW_SEARCH,
        ]);

        return array_merge($result, $fields);
    }
}
