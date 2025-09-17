<?php

namespace MetaFox\Report\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User;
use MetaFox\Report\Models\ReportItem;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Interface ReportItem.
 *
 * @mixin BaseRepository
 * @method ReportItem getModel()
 * @mixin CollectTotalItemStatTrait
 * @method ReportItem find($id, $columns = ['*'])()
 */
interface ReportItemRepositoryInterface
{
    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     *
     * @return bool|ReportItem
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function createReport(User $context, array $attributes);

    /**
     * @param User   $context
     * @param int    $itemId
     * @param string $itemType
     *
     * @return bool
     */
    public function canReport(User $context, int $itemId, string $itemType): bool;

    public function getReportItem(array $conditions);
}
