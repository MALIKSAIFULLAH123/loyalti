<?php

namespace MetaFox\Chat\Support;

use Exception;
use Illuminate\Support\Facades\Artisan;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Chat\Contracts\ChatContract;

class Chat implements ChatContract
{
    public function disableChat(string $package, bool $optimizeClear = true): void
    {
        try {
            $otherPackage = resolve(PackageRepositoryInterface::class)->getModel()
                ->newQuery()->where([
                    'name' => $package,
                ])->first();

            if (!$otherPackage || !$otherPackage->is_active) {
                return;
            }

            $package = resolve(PackageRepositoryInterface::class)->getModel()
                ->newQuery()->where([
                    'name' => 'metafox/chat',
                ])->first();

            if (!$package) {
                return;
            }

            $package->is_active = 0;

            $package->save();

            $package->refresh();

            if ($optimizeClear) {
                Artisan::call('optimize:clear');
            }
        } catch (Exception) {
            // Silent error
        }
    }
}
