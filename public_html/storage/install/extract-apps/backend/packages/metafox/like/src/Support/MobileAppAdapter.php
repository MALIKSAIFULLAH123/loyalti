<?php

namespace MetaFox\Like\Support;

use Illuminate\Database\Eloquent\Collection;
use MetaFox\Like\Contracts\MobileAppAdapter as MobileAppAdapterContracts;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Platform\MetaFox;

/**
 * Class CacheManager.
 * @ignore
 * @codeCoverageIgnore
 */
class MobileAppAdapter implements MobileAppAdapterContracts
{
    public function toCompatibleData($index, $version): int
    {
        if (!MetaFox::isMobile() || !version_compare(MetaFox::getApiVersion(), $version, '<')) {
            return $index;
        }

        $reactions = self::getReactionsForConfig();

        $result = $index;
        foreach ($reactions as $key => $reaction) {
            if ($index != $key + 1) {
                continue;
            }

            $result = $reaction->entityId();
        }

        return $result;
    }

    public function transformLegacyData($id, $version): int
    {
        if (!MetaFox::isMobile() || !version_compare(MetaFox::getApiVersion(), $version, '<')) {
            return $id;
        }

        $reactions = self::getReactionsForConfig();
        $result    = $id;
        foreach ($reactions as $key => $reaction) {
            if ($reaction->entityId() == $id) {
                $result = $key + 1;
            }
        }

        return $result;
    }

    public function getReactionsForConfig(): Collection
    {
        $repository = resolve(ReactionRepositoryInterface::class);
        return $repository->getReactionsForConfig();
    }
}
