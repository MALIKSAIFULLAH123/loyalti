<?php

namespace MetaFox\Page\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Page\Http\Resources\v1\Page\PagePreview;
use MetaFox\Platform\Contracts\User;

class UserPreviewListener
{
    /**
     * @param  User         $resource
     * @return JsonResource
     */
    public function handle(User $resource): JsonResource
    {
        return new PagePreview($resource);
    }
}
