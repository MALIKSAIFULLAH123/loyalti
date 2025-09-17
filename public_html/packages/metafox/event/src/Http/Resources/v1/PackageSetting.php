<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Event\Http\Resources\v1;

use MetaFox\Event\Http\Resources\v1\Category\CategoryItemCollection;
use MetaFox\Event\Repositories\CategoryRepositoryInterface;

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
    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @return array<string, mixed>
     */
    public function getMobileSettings(CategoryRepositoryInterface $categoryRepository): array
    {
        return [
            'categories' => new CategoryItemCollection($categoryRepository->getCategoryForFilter()),
        ];
    }
}
