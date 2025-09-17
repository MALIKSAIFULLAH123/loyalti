<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use Foxexpert\Sevent\Repositories\TicketRepositoryInterface;
use MetaFox\Payment\Http\Resources\v1\Order\GatewayForm;
use Foxexpert\Sevent\Models\Sevent;

class PaymentSeventForm extends GatewayForm
{
    public function boot(?int $id = null): void
    {   
        $this->resource = resolve(TicketRepositoryInterface::class)->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('payment::phrase.select_payment_gateway'))
            ->action('sevent-invoice')
            ->secondAction('@redirectTo')
            ->asPost()
            ->setValue([
                'id' => $this->resource->entityId()
        ]);
    }

    protected function addMoreBasicFields(Section $basic): void
    {
        parent::addMoreBasicFields($basic);

        $basic->addFields(
            Builder::hidden('id')
        );
    }

    protected function getGatewayParams(): array
    {
        $price = $this->resource->amount;
        $qty   = $this->resource->temp_qty;

        $price = $price * $qty;

        if (!$price) {
            $price = 0;
        }
        
        $sevent = Sevent::find($this->resource->sevent_id);
        return array_merge(parent::getGatewayParams(), [
            'payee_id' => $sevent->user_id,
            'price'    => $price,
        ]);
    }
}
