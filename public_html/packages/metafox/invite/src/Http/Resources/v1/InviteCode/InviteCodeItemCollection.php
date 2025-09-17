<?php

namespace MetaFox\Invite\Http\Resources\v1\InviteCode;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class InviteCodeItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class InviteCodeItemCollection extends ResourceCollection
{
    public $collects = InviteCodeItem::class;
}
