<?php

namespace MetaFox\Core\Support;

use Illuminate\Support\Manager;
use MetaFox\Core\Support\Converters\NoneConverter;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\Driver;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\FileConverterInterface;

/**
 * @method FileConverterInterface driver(?string $driver)
 */
class ConverterManager extends Manager
{
    /**
     * @property array<string, mixed>
     */
    private array $typeMap;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        [$drivers, $map] = $this->loadConverterDrivers();

        foreach ($drivers as $key => $handlerInstance) {
            $this->extend($key, fn () => $handlerInstance);
        }

        $this->typeMap = $map;
    }

    public function getDefaultDriver(): string
    {
        return 'none';
    }

    protected function createNoneDriver()
    {
        return resolve(NoneConverter::class);
    }

    /**
     * @param  string                      $mimeType
     * @return FileConverterInterface|null
     */
    public function makeConverter(string $mimeType): ?FileConverterInterface
    {
        $converter = null;
        $driver    = $this->getDriver($mimeType);

        try {
            $converter = $driver ? $this->driver($driver) : null;
        } catch (\Throwable) {
            // Just silent the exception
        }

        return $converter;
    }

    /**
     * @return array<string>
     */
    public function getAllowableTypes(): array
    {
        return array_keys($this->typeMap);
    }

    protected function getDriver(string $mimeType): ?string
    {
        return $this->typeMap[$mimeType] ?? null;
    }

    /**
     * @return array<int, mixed>
     */
    protected function loadConverterDrivers(): array
    {
        return localCacheStore()->rememberForever(__CLASS__ . __METHOD__, function () {
            $handlers = $typeMap = [];

            $drivers = app('core.drivers')->getDrivers(Constants::DRIVER_TYPE_FILE_CONVERTER, null, null);
            foreach ($drivers as $driver) {
                if (!$driver instanceof Driver) {
                    continue;
                }

                $handler = $driver->driver;
                $name    = $driver->name;

                if (!class_exists($handler)) {
                    continue;
                }

                $instance = app()->make($handler);

                if (!$instance instanceof FileConverterInterface) {
                    continue;
                }

                Arr::set($handlers, $name, $instance);

                $mimeTypes = $instance->supportedMimeTypes();

                foreach ($mimeTypes as $type) {
                    $typeMap[$type] = $name;
                }
            }

            return [$handlers, $typeMap];
        });
    }
}
