<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Forum\Http\Resources\v1;

use MetaFox\Forum\Repositories\ForumRepositoryInterface;

/**
 * | stub: src/Http/Resources/v1/PackageSetting.stub
 */

/**
 * Class PackageSetting.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSetting
{
    public function getMobileSettings(ForumRepositoryInterface $repository): array
    {
        return [
            'categories' => $repository->getForumsForView(user()),
        ];
    }
}
