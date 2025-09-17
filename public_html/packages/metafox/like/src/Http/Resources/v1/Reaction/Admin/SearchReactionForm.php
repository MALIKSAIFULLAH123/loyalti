<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Like\Models\Reaction as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchReactionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchReactionForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action('/like/reaction')
            ->acceptPageParams(['q', 'is_active'])
            ->setValue(['is_active' => null]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic()->asHorizontal();
        $basic->addFields(
            Builder::text('q')
                ->forAdminSearchForm()
                ->placeholder(__p('core::phrase.search_dot')),
            Builder::choice('is_active')
                ->forAdminSearchForm()
                ->label(__p('core::phrase.status'))
                ->options([
                    [
                        'label' => __p('core::phrase.all'),
                        'value' => null,
                    ],
                    [
                        'label' => __p('like::phrase.in_active'),
                        'value' => 0,
                    ],
                    [
                        'label' => __p('like::phrase.active'),
                        'value' => 1,
                    ],
                ]),
            Builder::submit()->forAdminSearchForm(),
        );
    }
}
