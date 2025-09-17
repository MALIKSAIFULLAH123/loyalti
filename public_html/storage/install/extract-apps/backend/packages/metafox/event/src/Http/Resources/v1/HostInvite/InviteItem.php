<?php

namespace MetaFox\Event\Http\Resources\v1\HostInvite;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Event\Models\Event;
use MetaFox\Event\Models\HostInvite as Model;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Support\ResourcePermission;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\User\Http\Resources\v1\User\UserItem;

/**
 * Class InviteItem.
 * @property Model $resource
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InviteItem extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request                 $request
     * @return array<string, mixed>
     * @throws AuthenticationException
     * @throws Exception
     */
    public function toArray($request)
    {
        $owner              = $this->resource->owner;
        $expiredAt          = $this->resource->expired_at;
        $expiredHours       = null;
        $expiredDescription = null;

        if ($expiredAt !== null) {
            $expiredHours       = Carbon::now()->floatDiffInHours($expiredAt);
            $expiredDescription = __p(
                'event::phrase.expired_invite_hours',
                [
                    'value' => CarbonInterval::make(round($expiredHours, 1) . 'h')
                        ->locale($owner?->preferredLocale())
                        ->cascade()
                        ->forHumans(),
                ]
            );
        }

        return [
            'id'                  => $this->resource->entityId(),
            'module_name'         => 'event',
            'resource_name'       => $this->resource->entityType(),
            'status_id'           => $this->resource->status_id,
            'event_id'            => $this->resource->event_id,
            'user'                => new UserItem($this->resource->user),
            'owner'               => new UserItem($owner),
            'extra'               => $this->getExtra(),
            'expired_hours'       => $expiredHours,
            'expired_description' => $expiredDescription,
        ];
    }

    /**
     * @throws AuthenticationException
     */
    protected function getExtra(): array
    {
        $policy  = PolicyGate::getPolicyFor(Event::class);
        $context = user();
        $invite  = $this->resource;

        if (!$policy instanceof EventPolicy) {
            abort(400, 'Missing Policy');
        }

        return [
            ResourcePermission::CAN_REMOVE_INVITE => $policy->removeInviteHost($context, $invite),
        ];
    }
}
