<?php

namespace MetaFox\App\Http\Resources\v1\Package\Admin;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;

/**
 * Class PackageItemCollection.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class PurchasedPackageItemCollection extends ResourceCollection
{
    public $collects = PurchasedPackageItem::class;
}
