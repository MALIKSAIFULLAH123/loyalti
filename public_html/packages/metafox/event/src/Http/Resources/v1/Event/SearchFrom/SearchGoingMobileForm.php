<?php

namespace MetaFox\Event\Http\Resources\v1\Event\SearchFrom;

use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\ViewScope;
use MetaFox\Event\Models\Event as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class SearchPastMobileForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchGoingMobileForm extends SearchMobileForm
{
    protected function getValues(): array
    {
        return [
            'sort' => SortScope::SORT_DEFAULT,
            'view' => ViewScope::VIEW_GOING,
        ];
    }
}
