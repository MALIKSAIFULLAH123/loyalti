<?php

namespace MetaFox\Core\Listeners;

use Illuminate\Support\Facades\Log;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Platform\PackageManager;
use Psy\Exception\ErrorException;

/**
 * Class PackageScanListener.
 */
class PackageScanListener
{
    /**
     * @var array<int, int|string>
     */
    private array $packages;

    /**
     * @var PackageRepositoryInterface
     */
    private PackageRepositoryInterface $packageRepository;

    /**
     * PackageScanListener constructor.
     *
     * @param PackageRepositoryInterface $packageRepository
     */
    public function __construct(PackageRepositoryInterface $packageRepository)
    {
        $this->packages          = PackageManager::getPackageNames();
        $this->packageRepository = $packageRepository;
    }

    /**
     * @return array<int, int|string>
     */
    private function getPackages(): array
    {
        return $this->packages;
    }

    /**
     * @throws ErrorException
     */
    public function handle(): void
    {
//        $packages = $this->getPackages();
//
//        Log::channel('installation')->info('checking scan ', $packages);
//
//        foreach ($packages as $name) {
//            if (!is_string($name)) {
//                continue;
//            }

//            $json = PackageManager::getComposerJson($name);
//
//            $this->packageRepository->createPackageByComposerJsonFile($json);
//        }
    }
}
