<?php

namespace MetaFox\Giphy\Http\Resources\v1\Gif;

use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;

/**
 * Class SearchGifMobileForm.
 * @ignore
 * @codeCoverageIgnore
 */
class SearchGifMobileForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action('giphy/gif/search')
            ->acceptPageParams(['q', 'limit', 'offset', 'rating', 'lang', 'bundle'])
            ->setValue([])
            ->asGet();
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->label(__p('giphy::phrase.search_gif'))
                    ->placeholder(__p('giphy::phrase.search_gif'))
                    ->maxLength(255),
            );
    }
}
