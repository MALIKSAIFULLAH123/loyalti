<?php
namespace MetaFox\Core\Listeners;

use MetaFox\Core\Repositories\DriverRepositoryInterface;

class GetPackageIdByEntityListener
{
    public function handle(string $name): ?string
    {
        return resolve(DriverRepositoryInterface::class)->getPackageIdByEntityType($name);
    }
}
