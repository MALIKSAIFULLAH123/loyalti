<?php

namespace MetaFox\User\Http\Resources\v1\User;

use MetaFox\User\Http\Resources\v1\User\Admin\DenyUserForm as DenyUserFormAdmin;
use MetaFox\User\Models\User as Model;

/**
 * Class DenyUserForm.
 *
 * @property Model $resource
 * @driverType form
 * @driverName user.deny_user
 */
class DenyUserForm extends DenyUserFormAdmin
{
    protected function prepare(): void
    {
        $this->action("user/deny/{$this->userId}")
            ->asPatch()
            ->setValue([]);
    }
}
