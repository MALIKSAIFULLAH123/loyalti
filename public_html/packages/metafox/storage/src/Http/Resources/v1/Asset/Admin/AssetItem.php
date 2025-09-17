<?php

namespace MetaFox\Storage\Http\Resources\v1\Asset\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\App\Models\Package;
use MetaFox\Storage\Models\Asset as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class AssetItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class AssetItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        try {
            $module     = resolve('core.packages')->getPackageByName($this->resource->package_id);
            $moduleName = $module instanceof Package ? $module->title : '';
        } catch (\Throwable $exception) {
            $moduleName = '';
        }

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'module_id'    => $this->module_id,
            'module_name'  => $moduleName,
            'is_modified'  => $this->resource->isModified(),
            'preview_data' => [
                'url'       => $this->resource->url,
                'file_name' => basename($this->resource->url),
                'file_type' => $this->resource->file_mime_type,
            ],
        ];
    }
}
