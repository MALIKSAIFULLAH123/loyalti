<?php

namespace MetaFox\User\Http\Resources\v1\ExportProcess\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class ExportProcessItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class ExportProcessItemCollection extends ResourceCollection
{
    public $collects = ExportProcessItem::class;
}
