<?php

namespace MetaFox\User\Listeners;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\User\Http\Resources\v1\User\UserPreview;

class UserPreviewListener
{
    /**
     * @param  ContractUser $resource
     * @return JsonResource
     */
    public function handle(ContractUser $resource): JsonResource
    {
        return new UserPreview($resource);
    }
}
