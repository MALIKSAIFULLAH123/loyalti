<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Ticket;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Repositories\Eloquent\SeventRepository;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class TicketItem.
 * @property Ticket $resource
 */
class TicketItem extends JsonResource
{
    use HasStatistic;
    use HasExtra;
    use IsLikedTrait;
    use IsFriendTrait;

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        return [
            'total_like'       => $this->resource->total_like,
            'total_view'       => $this->resource->total_view,
            'total_share'      => $this->resource->total_share,
            'total_comment'    => $this->resource->total_comment, // @todo improve or remove.
            'total_attachment' => $this->resource->total_attachment,
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws AuthenticationException
     */
    public function toArray($request): array
    {
        $context    = user();
        $userCurrency = app('currency')->getUserCurrencyId($context);
        $formattedAmount = app('currency')->getPriceFormatByCurrencyId($userCurrency, (float)$this->resource->amount);
       
        $remainingQty = $this->resource->qty - $this->resource->total_sales;
        $remainingQty = $remainingQty < 0 ? 0 : $remainingQty;

        $sevent = Sevent::find($this->resource->sevent_id);
        
        $eventIsExpiry = false;
        if ($sevent)
            $eventIsExpiry = resolve(SeventRepository::class)->isExpiry($sevent);

        $ticketIsExpiry = resolve(SeventRepository::class)->isTicketExpiry($this->resource);
        $extra = $this->getExtra();
        if ($extra['can_edit'] == true)
            $extra['can_edit'] = $this->resource->user_id === $context->entityId() || $context->hasAdminRole();
         if ($extra['can_delete'] == true)
            $extra['can_delete'] = $this->resource->user_id === $context->entityId() || $context->hasAdminRole();
            
        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'sevent',
            'event_is_expiry'   => $eventIsExpiry,
            'is_ticket_expiry'  => $ticketIsExpiry,
            'expiry_date'       => $this->resource->expiry_date,
            'resource_name'     => 'sevent_ticket',
            'title'             => $this->resource->title,
            'description'       => $this->resource->description,
            'amount'            => $this->resource->amount,
            'format_amount'     => $formattedAmount,
            'remaining_qty'     => $remainingQty,
            'qty'               => $this->resource->qty,
            'total_sales'      => $this->resource->total_sales,
            'is_unlimited'      => $this->resource->is_unlimited,
            'sevent_id'       => $this->resource->sevent_id,
            'module_id'         => 'sevent_ticket',
            'image'             => $this->resource->image,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'extra'             => $extra,
            'item_id'           => 0,
            'privacy'           => 1,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'statistic'         => $this->getStatistic(),
            'user'              => ResourceGate::user($this->resource->userEntity),
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
        ];
    }
}