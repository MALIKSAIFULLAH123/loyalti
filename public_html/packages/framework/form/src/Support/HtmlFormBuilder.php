<?php

namespace MetaFox\Form\Support;

use Illuminate\Support\Facades\Log;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;

class HtmlFormBuilder
{
    /**
     * @var array<string,string>
     */
    protected array $config = [];

    protected array $fallbacks = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $this->config = localCacheStore()->rememberForever(
            __CLASS__,
            fn () => array_reduce(
                resolve(DriverRepositoryInterface::class)
                ->loadDrivers(Constants::DRIVER_TYPE_FORM_FIELD, 'web'),
                function ($carry, $x) {
                    $carry[$x[0]] = $x[1];

                    return $carry;
                },
                []
            )
        );
    }

    /**
     * Get All Support field.
     *
     * @return string[]
     */
    public function getFields(): array
    {
        return $this->config;
    }

    public function getCreator(string $name): ?string
    {
        $creator = $this->config[$name] ?? $this->fallbacks[$name] ?? null;

        if (!$creator || !class_exists($creator)) {
            return null;
        }

        return $creator;
    }

    public function __call(string $name, array $arguments)
    {
        $creator = $this->getCreator($name);

        if (!$creator) {
            return null;
        }

        $name = $arguments[0] ?? null;

        $params = [];
        if ($name) {
            $params['name'] = $name;
        }

        return new $creator($params);
    }
}
