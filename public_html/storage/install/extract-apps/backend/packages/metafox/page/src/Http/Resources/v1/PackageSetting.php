<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1;


use MetaFox\Page\Http\Resources\v1\PageCategory\PageCategoryItemCollection;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;

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
     * @param PageCategoryRepositoryInterface $repository
     * @return array
     */
    public function getMobileSettings(PageCategoryRepositoryInterface $repository): array
    {
        return [
            'categories' => new PageCategoryItemCollection($repository->getCategoryForFilter()),
        ];
    }
}
