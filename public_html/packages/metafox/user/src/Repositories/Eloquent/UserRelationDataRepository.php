<?php

namespace MetaFox\User\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\User\Models\UserRelationData;
use MetaFox\User\Repositories\UserRelationDataRepositoryInterface;

class UserRelationDataRepository extends AbstractRepository implements UserRelationDataRepositoryInterface
{
    public function model()
    {
        return UserRelationData::class;
    }
}
