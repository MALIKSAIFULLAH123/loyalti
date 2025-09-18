<?php

namespace MetaFox\Page\Http\Resources\v1\Page;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName page.search_in_owner
 * @driverType form
 * @preload    1
 */
class SearchInOwnerMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('page')
            ->acceptPageParams(['q'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic(['component' => 'SFScrollView']);

        $basic->addFields(
            Builder::text('q')
                ->forBottomSheetForm('SFSearchBox')
                ->delayTime(200)
                ->placeholder(__p('page::phrase.search_pages'))
                ->className('mb2'),
        );
    }
}
