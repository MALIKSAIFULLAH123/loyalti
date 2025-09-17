<?php

namespace MetaFox\ActivityPoint\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use MetaFox\ActivityPoint\Models\PointSetting;
use MetaFox\ActivityPoint\Repositories\Eloquent\PointSettingRepository;
use MetaFox\ActivityPoint\Support\ActivityPoint as SupportActivityPoint;
use MetaFox\Authorization\Repositories\Contracts\RoleRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\ActivityPoint\Support\Facade\ActionType;
use MetaFox\ActivityPoint\Models\ActionType as ActionTypeModel;

/**
 * Class PackageSeeder.
 * @ignore
 * @codeCoverageIgnore
 */
class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->installSettings();
        $this->handleActionTypes();
        $this->seedingStatistics();
    }

    protected function installSettings(): void
    {
        $now         = Carbon::now();
        $defaultData = [
            'points'     => 0,
            'max_earned' => 0,
            'period'     => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        //Seed custom setting register from other app
        $this->getActivityPointService()->installCustomPointSettings($defaultData);

        $actions                = PointSetting::POINT_SETTING_ACTIONS;
        $driverRepo             = resolve(DriverRepositoryInterface::class);
        $entities               = $driverRepo->loadDrivers(Constants::DRIVER_TYPE_ENTITY, null);
        $allRoles               = resolve(RoleRepositoryInterface::class)->all()->pluck('id')->toArray();
        $pointSettingRepository = resolve(PointSettingRepository::class);
        $insertData             = [];

        foreach ($allRoles as $role) {
            foreach ($entities as $entity) {
                [, $driver, , $alias, $packageId] = $entity;
                $resource                         = resolve($driver);

                if (!$resource instanceof Content) {
                    continue;
                }

                if ($this->getActivityPointService()->isCustomInstalled($packageId)) {
                    continue;
                }

                // todo improve performance ?

                foreach ($actions as $action) {
                    $name        = sprintf('%s.%s', $resource->entityType(), $action);
                    $description = sprintf('activitypoint::phrase.%s_%s_description', $resource->entityType(), $action);

                    $insertData[$role . $name] = array_merge($defaultData, [
                        'name'               => $name,
                        'action'             => $action,
                        'module_id'          => $alias,
                        'role_id'            => $role,
                        'package_id'         => $packageId,
                        'description_phrase' => $description,
                    ]);
                }
            }
        }

        $pointSettingRepository->getModel()->newQuery()->upsert(array_values($insertData), ['name', 'role_id'], ['name', 'role_id', 'description_phrase']);
    }

    private function handleActionTypes(): void
    {
        try {
            $shouldMigrate = ActionTypeModel::query()->count() === 0;

            ActionType::setupDefaultActionTypes();
            ActionType::setupCustomActionTypes();
            ActionType::setupActionTypesInterpolateFromTransaction();

            if ($shouldMigrate) {
                ActionType::migrateTransactionExistPointSetting();
                ActionType::migrateTransactionNotExistPointSetting();
            }
        } catch (\Exception $exception) {
            logger('Error when migration apt_transactions', [$exception->getMessage()]);
        }
    }

    private function seedingStatistics(): void
    {
        // todo can not upgrade when there are 100K users.
        // Use raw sql to insert ... select ... from
        if (DB::getDriverName() === 'mysql') {
            DB::statement(DB::raw('insert ignore into apt_statistics (id) select id from user_activities;')->getValue(DB::getQueryGrammar()));
        } else {
            DB::statement(DB::raw('insert into apt_statistics (id) select id from user_activities on conflict do nothing;')->getValue(Db::getQueryGrammar()));
        }
    }

    protected function getActivityPointService(): SupportActivityPoint
    {
        return resolve(SupportActivityPoint::class);
    }
}
