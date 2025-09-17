<?php

namespace MetaFox\Notification\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use MetaFox\Notification\Models\NotificationModule;
use MetaFox\Notification\Repositories\NotificationModuleRepositoryInterface;
use MetaFox\Notification\Support\Browse\Scopes\ModuleScope;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\PackageScope;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class NotificationModuleRepository.
 */
class NotificationModuleRepository extends AbstractRepository implements NotificationModuleRepositoryInterface
{
    public function model()
    {
        return NotificationModule::class;
    }

    /**
     * @inheritDoc
     */
    public function getModulesByChannel(string $channel = 'mail'): Collection
    {
        return $this->getModel()->newQuery()
            ->where('is_active', true)
            ->addScope(resolve(PackageScope::class, [
                'table' => $this->getModel()->getTable(),
            ]))
            ->where('channel', $channel)
            ->orderBy('ordering')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function viewModules(array $attributes): Collection
    {
        $moduleId = Arr::get($attributes, 'module_id');
        $query    = $this->getModel()->newQuery()
            ->select('module_id');

        if ($moduleId) {
            $moduleScope = new ModuleScope();
            $moduleScope->setModuleId($moduleId);
            $query = $query->addScope($moduleScope);
        }

        $query->join('packages', 'packages.alias', '=', 'module_id')
            ->addSelect(["module_id", 'packages.title as packages_title']);

        return $query->groupBy('module_id', 'packages_title')->orderBy('packages_title')->get();
    }

    /**
     * @inheritDoc
     */
    public function toggleChannel(string $module, string $channel, int $active): NotificationModule
    {
        return $this->updateOrCreate([
            'module_id' => $module,
            'channel'   => $channel,
        ], [
            'is_active' => $active,
        ]);
    }
}
