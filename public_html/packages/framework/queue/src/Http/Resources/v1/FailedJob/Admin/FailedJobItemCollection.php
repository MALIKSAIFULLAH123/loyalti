<?php

namespace MetaFox\Queue\Http\Resources\v1\FailedJob\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * |--------------------------------------------------------------------------
 * | Resource Pattern
 * |--------------------------------------------------------------------------
 * | stub: /packages/resources/item_collection.stub.
 */

/**
 * Class FailedJobItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class FailedJobItemCollection extends ResourceCollection
{
    public $collects = FailedJobItem::class;
}
