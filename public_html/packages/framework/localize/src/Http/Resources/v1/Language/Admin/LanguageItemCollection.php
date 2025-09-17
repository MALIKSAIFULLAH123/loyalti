<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

class LanguageItemCollection extends ResourceCollection
{
    public $collects = LanguageItem::class;
}
