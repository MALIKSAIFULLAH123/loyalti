<?php

namespace MetaFox\Video\Http\Resources\v1\Video\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\AppSetting\ResourceExtraTrait;
use MetaFox\Video\Policies\VideoPolicy;

/**
 * Trait HasExtra.
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

        $resourceExtra = $this->getResourceExtra($resource, $context);
        $this->handleExtraPermission($resourceExtra);

        return $resourceExtra;
    }

    protected function handleExtraPermission(array &$resourceExtra): void
    {
        $permissions = ['can_like', 'can_share', 'can_comment'];

        foreach ($permissions as $permission) {
            $resourceExtra[$permission] = $this->resource->is_success && Arr::get($resourceExtra, $permission, false);
        }

        $policy = new VideoPolicy();

        $resourceExtra['can_view_mature'] = $policy->viewMatureContent(user(), $this->resource);
        $resourceExtra['can_edit_mature'] = $policy->updateMature(user(), $this->resource);
    }
}
