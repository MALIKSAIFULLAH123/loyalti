<?php

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Localize\Models\Language as Model;

/**
 * Class LanguageItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class LanguageItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->resource->entityId(),
            'language_code' => $this->resource->language_code,
            'module_name'   => 'core',
            'resource_name' => $this->resource->entityType(),
            'name'          => $this->resource->name,
            'updated_at'    => $this->resource->updated_at,
            'direction'     => $this->resource->direction,
            'charset'       => $this->resource->charset,
            'created_at'    => $this->resource->created_at,
            'is_master'     => $this->resource->is_master,
            'is_active'     => $this->resource->is_default ? null : $this->resource->is_active,
            'is_default'    => $this->resource->is_default,
            'links'         => ['phrases' => '/localize/phrase/browse?locale=' . $this->resource->language_code],
        ];
    }
}
