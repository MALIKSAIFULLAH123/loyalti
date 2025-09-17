<?php

namespace MetaFox\Mfa\Http\Resources\v1\Service;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Mfa\Models\Service as Model;
use MetaFox\Mfa\Repositories\UserServiceRepositoryInterface;
use MetaFox\Mfa\Support\Facades\Mfa;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class ServiceItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class ServiceItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        /** @var UserServiceRepositoryInterface $serviceRepository */
        $serviceRepository     = resolve(UserServiceRepositoryInterface::class);
        $context               = user();
        $service               = Mfa::service($this->resource->name);
        $resolution            = $request->get('resolution', 'web');
        $isActive              = (int) $serviceRepository->isServiceActivated($context, $this->resource->name);
        $isEnable              = !(!$isActive && !$service->isConfigurable($context));

        return [
            'service'      => $this->resource->name,
            'title'        => $service->toTitle(),
            'description'  => $service->toDescription(),
            'is_available' => $this->resource->is_active,
            'is_active'    => $isActive,
            'is_enable'    => $isEnable,
            'icon'         => $service->toIcon($resolution),
        ];
    }
}
