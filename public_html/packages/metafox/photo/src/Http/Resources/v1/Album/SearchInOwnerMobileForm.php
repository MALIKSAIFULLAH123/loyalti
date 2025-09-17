<?php

namespace MetaFox\Photo\Http\Resources\v1\Album;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @driverName photo_album.search_in_owner
 * @driverType form
 * @preload    1
 */
class SearchInOwnerMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('photo-album')
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
                ->placeholder(__p('photo::phrase.search_albums'))
                ->className('mb2'),
        );
    }
}
