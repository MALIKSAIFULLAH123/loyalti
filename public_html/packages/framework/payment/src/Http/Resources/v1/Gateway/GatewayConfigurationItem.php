<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway;

use Illuminate\Http\Request;
use MetaFox\Payment\Models\Gateway as Model;

/**
 * Class GatewayItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class GatewayConfigurationItem extends GatewayItem
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request      $request
     * @return array<mixed>
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);

        return array_merge($data, [
            'form_api_url' => $this->getFormApiUrl(),
        ]);
    }

    protected function getFormApiUrl(): ?string
    {
        if (null === $this->resource->service_class) {
            return null;
        }

        $serviceClass = resolve($this->resource->service_class);

        if (!is_object($serviceClass)) {
            return null;
        }

        return $serviceClass->getFormApiUrl();
    }
}
