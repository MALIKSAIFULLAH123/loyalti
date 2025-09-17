<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Invoice;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Http\Requests\v1\Invoice\SearchFormRequest;
use Foxexpert\Sevent\Models\Invoice as Model;
use Foxexpert\Sevent\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchBoughtInvoiceForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName sevent_invoice.bought_search
 * @driverType form
 * @preload    1
 */
class SearchBoughtInvoiceForm extends AbstractForm
{
    protected ?int $defaultListingId = null;

    protected string $view = ViewScope::VIEW_BOUGHT;

    public function boot(SearchFormRequest $request): void
    {
        $params                 = $request->all();
        $this->defaultListingId = Arr::get($params, 'sevent_id');
    }

    protected function prepare(): void
    {
        $this->action(apiUrl('sevent-invoice.index'))
            ->acceptPageParams(['view', 'sevent_id', 'from', 'to', 'status'])
            ->setValue([
                'sevent_id' => $this->defaultListingId,
                'status'     => 'all',
                'from'       => null,
                'to'         => null,
            ]);
    }

    protected function initialize(): void
    {
        $context = user();
        $basic = $this->addBasic()->asHorizontal();
        $userId = $context->entityId();

        if ($this->view === 'sold')
            $sevents = Sevent::where('user_id','=',$context->entityId())->get();
        else {      
            $columns = \Schema::getColumnListing('sevents');      
            $sevents = Sevent::join('sevent_invoices', function($join) use ($userId) {
                $join->on('sevent_invoices.sevent_id', '=', 'sevents.id')
                     ->where('sevent_invoices.user_id', '=', $userId);
            })
            ->select('sevents.*');

            foreach ($columns as $column) {
                $sevents->groupBy("sevents.$column");
            }

            $sevents = $sevents->get();
        }

        $seventOptions = [];
        foreach ($sevents as $sevent) {
            $seventOptions[] = [
                'label' => $sevent->title, 
                'value' => $sevent->id
            ];
        }
        $basic->addFields(
          Builder::choice('sevent_id')
                ->label(__p('sevent::phrase.sevent'))
                ->sxFieldWrapper($this->getResponsiveSx())
                ->marginNormal()
                ->options($seventOptions)
                ->sizeLarge(),
            Builder::date('from')
                ->forAdminSearchForm()
                ->label(__p('sevent::phrase.from'))
                ->maxDate(Carbon::now()->toISOString())
                ->sxFieldWrapper($this->getResponsiveSx())
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('sevent::phrase.from')]))
                ),
            Builder::date('to')
                ->forAdminSearchForm()
                ->label(__p('sevent::phrase.to'))
                ->maxDate(Carbon::now()->toISOString())
                ->sxFieldWrapper($this->getResponsiveSx())
                ->yup(
                    Yup::date()
                        ->nullable()
                        ->min(['ref' => 'from'])
                        ->setError('min', __p('sevent::phrase.the_end_time_should_be_greater_than_the_start_time'))
                        ->setError('typeError', __p('validation.date', ['attribute' => __p('sevent::phrase.to')]))
                ),
            Builder::choice('status')
                ->options($this->getPaymentStatus())
                ->forAdminSearchForm()
                ->disableClearable()
                ->label(__p('sevent::phrase.status')),
            Builder::submit()
                ->forAdminSearchForm(),
            Builder::clearSearchForm()
                ->label(__p('core::phrase.reset'))
                ->align('center')
                ->forAdminSearchForm()
                ->excludeFields(['view'])
                ->sizeMedium(),
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getPaymentStatus(): array
    {
        return [
            [
                'label' => __p('sevent::phrase.payment_status.all'),
                'value' => 'all',
            ],
            [
                'label' => __p('sevent::phrase.payment_status.pending_payment'),
                'value' => 'pending_payment',
            ],
            [
                'label' => __p('sevent::phrase.payment_status.pending_action'),
                'value' => 'init',
            ],
            [
                'label' => __p('sevent::phrase.payment_status.completed'),
                'value' => 'completed',
            ],
        ];
    }

    protected function getResponsiveSx(): array
    {
        return [
            'maxWidth' => [
                'xs' => '100%',
                'sm' => '50%',
                'md' => '220px',
            ],
            'width' => [
                'xs' => '100%',
                'sm' => '50%',
            ],
        ];
    }
}
