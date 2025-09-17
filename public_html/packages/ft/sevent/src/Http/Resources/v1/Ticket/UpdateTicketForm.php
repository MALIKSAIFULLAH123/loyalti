<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Ticket;

use Foxexpert\Sevent\Http\Requests\v1\Ticket\CreateFormRequest;
use Foxexpert\Sevent\Repositories\TicketRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Html\Submit;
use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateTicketForm.
 */
class UpdateTicketForm extends StoreTicketForm
{
    public function boot(CreateFormRequest $request, TicketRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        $this->setOwner($this->resource->owner);
    }

    protected function prepare(): void
    {
        $values = [
            'title'       => $this->resource->title,
            'amount'       => $this->resource->amount,
            'description'       => $this->resource->description,
            'qty'       => $this->resource->qty,
            'is_unlimited'       => $this->resource->is_unlimited,
            'sevent_id'       => $this->resource->sevent_id,
            'module_id'   => $this->resource->module_id,
            'expiry_date'       => $this->resource->expiry_date,
            'owner_id'    => $this->resource->owner_id
        ];
        
        $this->title(__p('sevent::phrase.edit_ticket'))
            ->action(url_utility()->makeApiUrl("sevent/ticket/{$this->resource->entityId()}"))
            ->setBackProps(__p('core::web.ticket'))
            ->asPut()
            ->setValue($values);
    }

    protected function buildPublishButton(): AbstractField
    {
        return new Submit([
            'label'     => __p('core::phrase.publish'),
            'flexWidth' => true,
        ]);
    }

    protected function buildSaveAsDraftButton(): AbstractField
    {
        return new Submit([
            'name'     => 'draft',
            'color'    => 'primary',
            'variant'  => 'outlined',
            'label'    => __p('core::phrase.update'),
            'value'    => 1,
            'showWhen' => ['falsy', 'published'],
        ]);
    }
}
