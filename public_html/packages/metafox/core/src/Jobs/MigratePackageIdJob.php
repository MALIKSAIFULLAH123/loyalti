<?php

namespace MetaFox\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Core\Models\StatsContent;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\PackageManager;

class MigratePackageIdJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $model = new StatsContent();

        $query = $model->newQuery()->select(['name', 'label'])->groupBy(['name', 'label']);

        $data = $query->get();

        /** @var StatsContent[] $data */
        foreach ($data as $item) {
            $key = $item->getRawOriginal('label');

            [$namespace, $group, $name] = app('translator')->parseKey($key);
            $packageId = PackageManager::getByAlias($namespace);

            StatsContent::query()
                ->where('name', $item->name)
                ->update([
                    'package_id' => $packageId,
                    'module_id'  => $namespace,
                ]);
        }
    }
}
