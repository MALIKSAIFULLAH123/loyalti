<?php

namespace MetaFox\Backup\Http\Resources\v1\File\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class FileItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class FileItemCollection extends ResourceCollection
{
    public $collects = FileItem::class;
}
