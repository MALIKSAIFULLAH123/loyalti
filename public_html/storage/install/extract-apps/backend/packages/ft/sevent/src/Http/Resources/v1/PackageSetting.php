<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace Foxexpert\Sevent\Http\Resources\v1;

use Foxexpert\Sevent\Http\Resources\v1\Category\Admin\CategoryItemCollection;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub.
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
