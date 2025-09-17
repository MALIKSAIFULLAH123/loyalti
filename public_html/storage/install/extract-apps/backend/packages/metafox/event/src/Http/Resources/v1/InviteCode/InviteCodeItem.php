<?php

namespace MetaFox\Event\Http\Resources\v1\InviteCode;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Models\InviteCode as Model;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class InviteItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InviteCodeItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     * @throws Exception
     */
    public function toArray($request)
    {
        $user               = $this->resource->user;
        $expiredAt          = $this->resource->expired_at;
        $expiredDay         = Carbon::now()->floatDiffInHours($expiredAt);
        $expiredDescription = __p(
            'event::phrase.expired_invite_hours',
            [
                'value' => CarbonInterval::make(round($expiredDay, 1) . 'h')
                    ->locale($user?->preferredLocale())
                    ->cascade()
                    ->forHumans(),
            ]
        );

        return [
            'id'                  => $this->resource->entityId(),
            'module_name'         => 'event',
            'resource_name'       => $this->resource->entityType(),
            'status'              => false,
            'event_id'            => $this->resource->event_id,
            'link'                => $this->resource->toLink(),
            'url'                 => $this->resource->toUrl(),
            'user'                => ResourceGate::asResource($user, 'detail'),
            'expired_day'         => $expiredDay,
            'expired_description' => $expiredDescription,
        ];
    }
}
