<?php

namespace MetaFox\Giphy\Http\Resources\v1\Gif;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchGifForm.
 * @ignore
 * @codeCoverageIgnore
 */
class SearchGifForm extends AbstractForm
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
