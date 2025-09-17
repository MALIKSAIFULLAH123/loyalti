<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Music\Http\Resources\v1;

use MetaFox\Music\Http\Resources\v1\Genre\GenreItemCollection;
use MetaFox\Music\Repositories\GenreRepositoryInterface;

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
    public function getMobileSettings(GenreRepositoryInterface $repository): array
    {
        return [
            'categories' => new GenreItemCollection($repository->getCategoryForFilter()),
        ];
    }
}
