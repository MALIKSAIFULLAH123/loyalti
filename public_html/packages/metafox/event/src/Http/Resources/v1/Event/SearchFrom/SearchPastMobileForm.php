<?php

namespace MetaFox\Event\Http\Resources\v1\Event\SearchFrom;

use Illuminate\Support\Arr;
use MetaFox\Event\Support\Browse\Scopes\Event\SortScope;
use MetaFox\Event\Support\Browse\Scopes\Event\WhenScope;
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
class SearchPastMobileForm extends SearchMobileForm
{
    protected function getValues(): array
    {
        return [
            'sort' => SortScope::SORT_DEFAULT,
            'when' => WhenScope::WHEN_PAST,
        ];
    }

    protected function getAcceptFieldBasic(): array
    {
        $params = self::ACCEPT_FIELD_DEFAULT;

        $params = array_filter($params, function ($item) {
            return 'when' !== $item;
        });

        return $params;
    }

    protected function getAcceptFieldBottom(): array
    {
        $params = self::ACCEPT_FIELD_DEFAULT;

        $params = array_filter($params, function ($item) {
            return 'when' !== $item;
        });

        return $params;
    }
}
