<?php

namespace Foxexpert\Sevent\Http\Resources\v1\UserTicket;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\Sevent;
use Illuminate\Support\Facades\Storage;
use Foxexpert\Sevent\Repositories\Eloquent\SeventRepository;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;

/**
 * Class UserTicketItem.
 * @property UserTicket $resource
 */
class UserTicketItem extends JsonResource
{
    use HasStatistic;
    use HasExtra;
    use IsLikedTrait;
    use IsFriendTrait;

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
        $sevent = Sevent::find($this->resource->sevent_id);
        $ticket = Ticket::find($this->resource->ticket_id);
        
        $pdfLink = null;
        if ($this->resource->pdf_file_id > 0) {
            $pdfFile = upload()->getFile($this->resource->pdf_file_id , true);
            $pdfLink = Storage::url($pdfFile->path);
        }

        return [
            'id'                => $this->resource->entityId(),
            'module_name'       => 'sevent',
            'sevent_id'         => $this->resource->sevent_id,
            'resource_name'     => 'sevent_user_ticket',
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'item_id'           => 0,
            'event'             => ResourceGate::asDetail($sevent),
            'ticket'            => $ticket,
            'pdf'               => $pdfLink,
            'paid_at'           => $this->resource->paid_at,
            'number'            => $this->resource->number,
            'qr'                => $this->resource->image
        ];
    }
}
