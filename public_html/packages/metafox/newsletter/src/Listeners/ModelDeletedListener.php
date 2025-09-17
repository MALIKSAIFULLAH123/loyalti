<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Newsletter\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Authorization\Models\Role;
use MetaFox\Localize\Models\Country;
use MetaFox\Newsletter\Models\CountryData;
use MetaFox\Newsletter\Models\GenderData;
use MetaFox\Newsletter\Models\RoleData;
use MetaFox\User\Models\UserGender;

/**
 * Class ModelDeletedListener.
 * @ignore
 */
class ModelDeletedListener
{
    /**
     * @param Model $model
     */
    public function handle(Model $model): void
    {
        $this->deletePivotData($model);
    }

    private function deletePivotData(Model $model): void
    {
        if ($model instanceof UserGender) {
            GenderData::query()->where('gender_id', $model->entityId())->delete();
        }

        if ($model instanceof Country) {
            CountryData::query()->where('country_iso', $model->country_iso)->delete();
        }

        if ($model instanceof Role) {
            RoleData::query()->where('role_id', $model->entityId())->delete();
        }
    }
}
