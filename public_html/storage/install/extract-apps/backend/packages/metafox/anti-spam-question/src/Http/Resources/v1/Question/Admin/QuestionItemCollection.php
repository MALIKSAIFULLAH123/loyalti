<?php

namespace MetaFox\AntiSpamQuestion\Http\Resources\v1\Question\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Http\Request;

/**
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item_collection.stub
*/

/**
 * Class QuestionItemCollection.
 * @ignore
 * @codeCoverageIgnore
 */
class QuestionItemCollection extends ResourceCollection
{
    public $collects = QuestionItem::class;
}
