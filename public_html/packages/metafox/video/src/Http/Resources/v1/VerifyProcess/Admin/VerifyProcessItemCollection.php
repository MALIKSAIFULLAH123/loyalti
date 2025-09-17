<?php

namespace MetaFox\Video\Http\Resources\v1\VerifyProcess\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class VerifyProcessItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class VerifyProcessItemCollection extends ResourceCollection
{
    public $collects = VerifyProcessItem::class;
}
