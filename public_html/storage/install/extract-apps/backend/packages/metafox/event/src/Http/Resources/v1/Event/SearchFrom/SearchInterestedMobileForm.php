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
 * Class SearchInterestedMobileForm
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchInterestedMobileForm extends SearchMobileForm
{
    protected function getValues(): array
    {
        return [
            'sort' => SortScope::SORT_DEFAULT,
            'view' => ViewScope::VIEW_INTERESTED,
        ];
    }
}
