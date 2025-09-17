<?php

namespace MetaFox\Notification\Http\Resources\v1\Notification;

use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationItemCollection extends ResourceCollection
{
    public $collects = NotificationItem::class;

    /**
     * toArray.
     *
     * @param  mixed        $request
     * @return array<mixed>
     */
    public function toArray($request): array
    {
        $collection = $this->collection->map(function ($item) use ($request) {
            try {
                return $item->toArray($request);
            } catch (Throwable $e) {
                // silent
                Log::error($e);
            }
        })->filter()->toArray();

        try {
            app('events')->dispatch('platform.resource_collection.override', [&$collection, $request, $this]);
        } catch (\Throwable $exception) {
            Log::error('override notification item collection error message: ' . $exception->getMessage());
            Log::error('override notification item collection error trace: ' . $exception->getTraceAsString());
        }

        return $collection;
    }
}
