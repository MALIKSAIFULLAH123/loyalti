<?php

namespace MetaFox\User\Repositories\Eloquent;

use App\Models\User;
use MetaFox\User\Repositories\AdminLoggedRepositoryInterface;

class AdminLoggedRepository implements AdminLoggedRepositoryInterface
{
    public function model()
    {
        return User::class;
    }

    public function getLatestLogin()
    {
        return $this->getModel()->newQuery()->get();
    }

    public function getActiveAdmin()
    {
        return $this->getModel()->newQuery()->get();
    }
}
