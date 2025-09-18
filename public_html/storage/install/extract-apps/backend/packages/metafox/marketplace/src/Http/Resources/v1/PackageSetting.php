<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Marketplace\Http\Resources\v1;

use MetaFox\Marketplace\Http\Resources\v1\Category\Admin\CategoryItemCollection;
use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;

/**
 | stub: src/Http/Resources/v1/PackageSetting.stub
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getMobileSettings(CategoryRepositoryInterface $repository): array
    {
        return [
            'categories' => new CategoryItemCollection($repository->getCategoryForFilter()),
        ];
    }
}
