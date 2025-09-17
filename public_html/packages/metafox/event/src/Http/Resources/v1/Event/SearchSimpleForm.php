<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Event\Support\Browse\Scopes\Event\ViewScope;
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
        $this->action('/event/search')->acceptPageParams(['q']);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('event::phrase.search_events'))
                ->className('mb2')
                ->marginNone()
                ->sx([
                    'flex' => 1,
                ]),
        );

        if (Settings::get('core.google.google_map_api_key') != null) {
            $basic->addFields(
                Builder::iconButton('icon')
                    ->linkTo('/event/search-map?view=' . ViewScope::VIEW_ON_MAP)
                    ->icon('ico-map-o')
                    ->tooltip('view_on_map'),
            );
        }
    }
}
