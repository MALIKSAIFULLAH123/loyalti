<?php

namespace MetaFox\Notification\Http\Resources\v1\Type\Admin;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Notification\Models\Type as Model;
use MetaFox\Notification\Models\TypeChannel;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Notification\Support\ChannelManager;

/**
 * Class TypeItem.
 *
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TypeItem extends JsonResource
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
        $obj = $this->resource;

        $module = $obj->require_module_id ?? $obj->module_id;
        $data   = [
            'id'               => $obj->id,
            'type'             => $obj->type,
            'module_id'        => $module,
            'module_name'      => $obj->packageName(),
            'title'            => __p($obj->title),
            'can_edit'         => $obj->can_edit,
            'is_request'       => $obj->is_request,
            'is_active'        => $obj->is_active,
            'is_system'        => $obj->is_system,
            'active_channels'  => $this->handleActiveChannels(),
            'disable_channels' => $this->handleDisableChannels(),
        ];

        return $data;
    }

    protected function handleActiveChannels(): array
    {
        $channels = [];
        foreach ($this->getAllActiveChannels() as $channel) {
            $channelName = $channel->name;
            $typeChannel = $this->resource->typeChannels->where('channel', $channelName)->first();

            $channels[$channelName] = $this->resource->is_active && $typeChannel instanceof TypeChannel && $typeChannel->is_active;
        }

        return $channels;
    }

    /**
     * Get the value of activeChannels.
     */
    public function getAllActiveChannels(): Collection
    {
        return resolve(NotificationChannelRepositoryInterface::class)->getActiveChannels();
    }

    protected function handleDisableChannels(): array
    {
        $channels = [];
        foreach ($this->getAllActiveChannels() as $channel) {
            $handler     = $this->resource->handler;
            $channelName = $channel->name;

            if (empty($handler)) {
                $channels[$channelName] = true;
                continue;
            }

            $hasSupportViaChannel = $this->channelManager()->hasSupportSendNotifyViaChannel(resolve($handler), $channelName);

            if (!$hasSupportViaChannel) {
                $channels[$channelName] = true;
                continue;
            }
        }

        return $channels;
    }

    protected function channelManager(): ChannelManager
    {
        return resolve(ChannelManager::class);
    }
}
