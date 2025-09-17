<?php

namespace MetaFox\Report\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Report\Models\ReportOwnerUser;
use MetaFox\Report\Repositories\ReportOwnerUserRepositoryInterface;

/**
 * @method ReportOwnerUser find($id, $columns = ['*'])
 * @method ReportOwnerUser getModel()
 */
class ReportOwnerUserRepository extends AbstractRepository implements ReportOwnerUserRepositoryInterface
{
    public function model(): string
    {
        return ReportOwnerUser::class;
    }
}
