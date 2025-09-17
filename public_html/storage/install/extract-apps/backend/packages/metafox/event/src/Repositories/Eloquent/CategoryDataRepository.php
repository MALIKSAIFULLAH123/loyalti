<?php

namespace MetaFox\Event\Repositories\Eloquent;

use MetaFox\Event\Models\CategoryData;
use MetaFox\Event\Repositories\CategoryDataRepositoryInterface;
use MetaFox\Platform\Repositories\AbstractRepository;

class CategoryDataRepository extends AbstractRepository implements CategoryDataRepositoryInterface
{
    public function model()
    {
        return CategoryData::class;
    }
}
