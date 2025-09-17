<?php

namespace MetaFox\Platform\Traits\Http\Resources;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\AppSetting\ResourceExtraTrait;
use stdClass;

/**
 * Trait HasExtra
 * @package MetaFox\Platform\Traits\Http\Request
 * @property Content|null $resource
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
trait HasExtra
{
    use ResourceExtraTrait;

    /**
     * @return array<string,           bool>
     * @throws AuthenticationException
     */
    protected function getExtra(): array
    {
        $context = user();

        $resource = $this->resource;

        if (!$this->resource instanceof Content) {
            return [];
        }

        $acl = $this->getResourceExtra($resource, $context);

        try {
            $proxy = new stdClass();

            foreach ($acl as $key => $value) {
                $proxy->{$key} = $value;
            }

            app('events')->dispatch('platform.acl.override_get_extra', [$proxy, $context, $resource]);

            $acl = (array) $proxy;
        } catch (\Throwable $exception) {
            Log::error(sprintf('override acl of resource %s error message: %s', $resource->entityType(), $exception->getMessage()));
            Log::error(sprintf('override acl of resource %s error trace: %s', $resource->entityType(), $exception->getTraceAsString()));
        }

        return $acl;
    }
}
