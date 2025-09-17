<?php

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\App\Models\Package;
use MetaFox\Menu\Models\Menu as Model;
use MetaFox\Platform\PackageManager;

/**
 * Class MenuItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class MenuItem extends JsonResource
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
        $package = null;
        try {
            $package  = app('core.packages')->getPackageByName($this->resource->package_id);
        } catch (\Throwable) {
            // Just silent the error package not found
        }

        $title = $this->resource->title;

        return [
            'id'            => $this->resource->id,
            'name'          => $this->resource->name,
            'title'         => $title ? $title : $this->resource->name,
            'resolution'    => $this->resource->resolution,
            'module_id'     => $this->resource->module_id,
            'url'           => $package instanceof Package ? sprintf('/menu/menu/%s/menu-item/browse', $this->resource->entityId()) : null,
            'app_name'      => $package instanceof Package ? $package?->title : __p('core::phrase.unknown'),
            'resource_name' => $this->resource->resource_name,
            'version'       => $this->resource->version,
            'is_active'     => $this->resource->is_active,
        ];
    }
}
