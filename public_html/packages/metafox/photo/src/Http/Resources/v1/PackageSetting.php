<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Photo\Http\Resources\v1;

use MetaFox\Photo\Http\Resources\v1\Category\CategoryItemCollection;
use MetaFox\Photo\Repositories\CategoryRepositoryInterface;

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
