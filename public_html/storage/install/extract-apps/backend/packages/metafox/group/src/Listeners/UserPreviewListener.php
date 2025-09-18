<?php

namespace MetaFox\Group\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Group\Http\Resources\v1\Group\GroupPreview;
use MetaFox\Platform\Contracts\User;

/**
 * Class UserPreviewListener.
 * @ignore
 */
class UserPreviewListener
{
    /**
     * @param  User|null    $resource
     * @return JsonResource
     */
    public function handle(?User $resource): JsonResource
    {
        return new GroupPreview($resource);
    }
}
