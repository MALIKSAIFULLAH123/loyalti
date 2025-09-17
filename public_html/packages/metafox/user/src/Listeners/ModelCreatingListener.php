<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\User\Listeners;

use MetaFox\User\Models\User as UserModel;
class ModelCreatingListener
{
    public function handle($model)
    {
        if ($model instanceof UserModel) {
            $this->addSearchName($model);
        }
    }

    protected function addSearchName(UserModel $user): void
    {
        $user->search_name = $user->display_name;
    }
}
