<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1\PageClaim;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Page\Support\Facade\PageClaim as PageClaimFacade;
use MetaFox\Platform\Support\Browse\Browse;

class SearchPageClaimForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/page-claim/search')
            ->acceptPageParams(['q', 'sort', 'when', 'status'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()
            ->asHorizontal()
            ->sxContainer(['alignItems' => 'unset']);

        $basic->addFields(
            Builder::text('q')
                ->placeholder(__p('page::phrase.search_page_claims'))
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx()),
            Builder::choice('sort')
                ->label(__p('core::phrase.sort_label'))
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->options([
                    ['label' => __p('core::phrase.sort.recent'), 'value' => 'recent'],
                    ['label' => __p('core::phrase.sort.most_liked'), 'value' => 'most_member'],
                ]),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
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
            Builder::choice('status')
                ->label(__p('core::phrase.status'))
                ->forAdminSearchForm()
                ->sxFieldWrapper($this->getResponsiveSx())
                ->options(PageClaimFacade::getAllowStatusOptions()),
            Builder::submit()
                ->forAdminSearchForm()
                ->label(__p('core::phrase.search')),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->forAdminSearchForm()
                ->align('right'),
        );
    }
    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
