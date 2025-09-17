<?php

namespace MetaFox\Attachment\Http\Resources\v1\FileType\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class FileTypeItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class FileTypeItemCollection extends ResourceCollection
{
    public $collects = FileTypeItem::class;
}
