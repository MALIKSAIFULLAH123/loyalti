<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Traits;

use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * Trait CollectTotalItemStatTrait.
 *
 * @mixin AbstractRepository
 */
trait CollectTotalItemStatTrait
{
    public function getTotalItemByPeriod(?\Carbon\Carbon $after = null, ?\Carbon\Carbon $before = null): int
    {
        $query = $this->getModel()->newModelQuery();

        if ($after) {
            $query->where('created_at', '>=', $after);
        }

        if ($before) {
            $query->where('created_at', '<=', $before);
        }

        return $query->count();
    }

    /**
     * @param \Carbon\Carbon|null $after
     * @param \Carbon\Carbon|null $before
     * @param array|null          $where
     */
    public function getTotalPendingItemByPeriod(?\Carbon\Carbon $after = null, ?\Carbon\Carbon $before = null, ?array $where = null): int
    {
        $query = $this->getModel()->newQuery();

        if ($after) {
            $query->where('created_at', '>=', $after);
        }

        if ($before) {
            $query->where('created_at', '<=', $before);
        }

        if (null === $where) {
            $query->where('is_approved', 0);
        }

        if (is_array($where)) {
            $query->where($where);
        }

        return $query->count();
    }
}
