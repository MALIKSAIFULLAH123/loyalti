<?php

namespace MetaFox\Friend\Http\Resources\v1\Friend;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Friend\Models\Friend as Model;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;

/**
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SearchFriendForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('/friend/search')
            ->acceptPageParams(['q', 'sort', 'when', 'is_featured', 'returnUrl'])
            ->setValue([
                'view' => Browse::VIEW_SEARCH,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::searchBox('q')
                ->placeholder(__p('friend::phrase.search_friends')),
            Builder::clearSearch()
                ->label(__p('core::phrase.reset'))
                ->align('right')
                ->excludeFields(['view', 'q']),
            Builder::choice('when')
                ->label(__p('core::phrase.when_label'))
                ->marginNormal()
                ->options(WhenScope::getWhenOptions()),
            Builder::switch('is_featured')
                ->label(__p('core::phrase.featured')),
        );
    }
}
