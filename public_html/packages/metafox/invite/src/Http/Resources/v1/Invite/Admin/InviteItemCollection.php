<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class InviteItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class InviteItemCollection extends ResourceCollection
{
    public $collects = InviteItem::class;
}
