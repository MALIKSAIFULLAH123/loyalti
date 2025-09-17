<?php

namespace MetaFox\Video\Support;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Contracts\VideoServiceInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Video\Contracts\ProviderManagerInterface;
use MetaFox\Video\Models\Video;

class ProviderManager implements ProviderManagerInterface
{
    public function __construct() {}

    protected function driverRepository(): DriverRepositoryInterface
    {
        return resolve(DriverRepositoryInterface::class);
    }

    public function getDefaultServiceClass(): VideoServiceInterface
    {
        $defaultService = Settings::get('video.video_service', MetaFoxConstant::VIDEO_SERVICE_DEFAULT);
        [, $serviceClass] = $this->driverRepository()->loadDriver(
            MetaFoxConstant::VIDEO_SERVICE_TYPE,
            $defaultService,
            MetaFoxConstant::RESOLUTION_ADMIN
        );

        Log::channel('dev')->info('Loading Video Service: ' . $serviceClass);

        $serviceClass = match ($defaultService) {
            'ffmpeg' => new $serviceClass([
                'item_type'  => Video::ENTITY_TYPE,
                'thumb_size' => ['500'],
            ]),
            default  => new $serviceClass(),
        };

        if (!$serviceClass instanceof VideoServiceInterface) {
            abort(400, __p('video::phrase.no_active_video_service'));
        }

        return $serviceClass;
    }

    public function getVideoServiceClassByDriver(string $driver): VideoServiceInterface
    {
        [, $serviceClass] = $this->driverRepository()->loadDriver(
            MetaFoxConstant::VIDEO_SERVICE_TYPE,
            $driver,
            MetaFoxConstant::RESOLUTION_ADMIN
        );

        $serviceClass = match ($driver) {
            'ffmpeg' => new $serviceClass([
                'item_type'  => Video::ENTITY_TYPE,
                'thumb_size' => ['500'],
            ]),
            default  => new $serviceClass(),
        };

        if (!$serviceClass instanceof VideoServiceInterface) {
            abort(400, __p('video::phrase.no_active_video_service'));
        }

        return $serviceClass;
    }

    public function checkReadyService(): bool
    {
        try {
            $service = $this->getDefaultServiceClass();

            if (method_exists($service, 'testConfig')) {
                return $service->testConfig();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getServicesOptions(): array
    {
        return $this->driverRepository()->getModel()
            ->newModelQuery()
            ->where('is_active', '=', 1)
            ->where('type', MetaFoxConstant::VIDEO_SERVICE_TYPE)
            ->get()
            ->collect()
            ->map(function ($service) {
                return [
                    'label' => __p($service->title),
                    'value' => $service->name,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * @inheritDoc
     */
    public function viewServices(User $context, array $params = []): EloquentCollection
    {
        return $this->driverRepository()
            ->getDrivers(
                MetaFoxConstant::VIDEO_SERVICE_TYPE,
                null,
                MetaFoxConstant::RESOLUTION_ADMIN
            );
    }

    public function getProcessingTimeout(): int
    {
        $defaultService = Settings::get('video.video_service', MetaFoxConstant::VIDEO_SERVICE_DEFAULT);

        return match ($defaultService) {
            'ffmpeg' => Settings::get('ffmpeg.timeout'),
            default  => Settings::get('queue.retry_timeout', 900),
        };
    }
}
