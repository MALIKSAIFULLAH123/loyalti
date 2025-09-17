<?php

namespace MetaFox\Platform\Http\Resources\Abstract;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection as LaravelResourceCollection;
use Illuminate\Support\Facades\Log;

class ResourceCollection extends LaravelResourceCollection
{
    /**
     * Transform the resource into a JSON array.
     *
     * @param  \Illuminate\Http\Request                                        $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request)
    {
        $collection = $this->collection->map->toArray($request)
            ->filter(function ($item) {
                return !empty($item);
            })->all();

        try {
            app('events')->dispatch('platform.resource_collection.override', [&$collection, $request, $this]);
        } catch (\Throwable $exception) {
            Log::error('override platform resource collection error message: ' . $exception->getMessage());
            Log::error('override platform resource collection error trace: ' . $exception->getTraceAsString());
        }

        return $collection;
    }
}
