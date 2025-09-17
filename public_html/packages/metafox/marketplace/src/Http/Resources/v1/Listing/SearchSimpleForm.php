<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Marketplace\Http\Resources\v1\Listing;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * @preload 1
 */
class SearchSimpleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/marketplace/search')
            ->acceptPageParams(['q']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('marketplace::phrase.search_listings'))
                ->className('mb2')
                ->marginNone()
                ->sx([
                    'flex' => 1,
                ]),
        );

        if (Settings::get('core.google.google_map_api_key') != null) {
            $basic->addFields(
                Builder::iconButton('icon')
                    ->linkTo('/marketplace/search-map')
                    ->icon('ico-map-o')
                    ->tooltip('view_on_map'),
            );
        }
    }
}
