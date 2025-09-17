<?php

namespace MetaFox\Core\Http\Resources\v1\AdminAccess;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Models\AdminAccess as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class AdminAccessItem.
 * @property Model $resource
 */
class AdminAccessItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'core',
            'resource_name'     => $this->resource->entityType(),
            'ip_address'        => $this->resource->ip_address,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
        ];
    }
}
