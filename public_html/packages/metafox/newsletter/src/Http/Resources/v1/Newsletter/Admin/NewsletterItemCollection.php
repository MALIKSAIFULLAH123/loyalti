<?php

namespace MetaFox\Newsletter\Http\Resources\v1\Newsletter\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
 */

/**
 * Class NewsletterItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class NewsletterItemCollection extends ResourceCollection
{
    public $collects = NewsletterItem::class;
}
