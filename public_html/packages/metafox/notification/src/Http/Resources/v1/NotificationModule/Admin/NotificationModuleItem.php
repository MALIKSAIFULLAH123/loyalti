<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationModule\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Notification\Models\NotificationModule as Model;

/*
|--------------------------------------------------------------------------
| Resource Pattern
|--------------------------------------------------------------------------
| stub: /packages/resources/item.stub
*/

/**
 * Class NotificationModuleItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @ignore
 * @codeCoverageIgnore
 * @mixin Model
 */
class NotificationModuleItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $obj  = $this->resource;
        $data = [
            'id'          => $obj->module_id,
            'module_id'   => $obj->module_id,
            'module_name' => app('core.packages')->getPackageByAlias($obj->module_id)?->title,
            'channels'    => $this->getChannels(),
        ];

        return $data;
    }

    protected function getChannels(): array
    {
        $channels = [];

        foreach ($this->resource->moduleChannels as $channel) {
            $channels[$channel->channel] = (bool) $channel->is_active;
        }

        return $channels;
    }
}
