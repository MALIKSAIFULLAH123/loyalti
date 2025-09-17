<?php

namespace Foxexpert\Sevent\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Foxexpert\Sevent\Models\Invoice;
use Foxexpert\Sevent\Models\UserTicket;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\Attend;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\InvoiceTransaction;
use Foxexpert\Sevent\Notifications\OwnerPaymentSuccessNotification;
use Foxexpert\Sevent\Notifications\PaymentPendingNotification;
use Foxexpert\Sevent\Notifications\PaymentSuccessNotification;
use Foxexpert\Sevent\Policies\InvoicePolicy;
use Foxexpert\Sevent\Repositories\InvoiceRepositoryInterface;
use Foxexpert\Sevent\Repositories\InvoiceTransactionRepositoryInterface;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use Foxexpert\Sevent\Repositories\TicketRepositoryInterface;
use Foxexpert\Sevent\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Helper\Pagination;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use MetaFox\Core\Support\FileSystem\UploadFile;

/**
 * Class InvoiceRepository.
 * @ignore
 * @codeCoverageIgnore
 */
class InvoiceRepository extends AbstractRepository implements InvoiceRepositoryInterface
{
    public function model()
    {
        return Invoice::class;
    }

    public function createInvoice(User $context, int $id, int $gatewayId): array
    {
        $ticket = resolve(TicketRepositoryInterface::class)->find($id);
        $price = $ticket->amount * $ticket->temp_qty;
        $ticketId = $ticket->entityId();
        $seventId = $ticket->sevent_id;

        $invoice = new Invoice();
      
        $userCurrency = app('currency')->getUserCurrencyId($context);
        $price = (float) $price;
        
        $invoice->fill([
            'ticket_id'       => $ticketId,
            'qty'             => $ticket->temp_qty,
            'sevent_id'     => $seventId,
            'user_id'         => $context->entityId(),
            'user_type'       => $context->entityType(),
            'price'           => $price,
            'currency_id'     => $userCurrency,
            'payment_gateway' => $gatewayId,
            'status'          => 'init',
            'paid_at'         => null,
        ]);
        $invoice->save();

        // reset temp value
        $ticket->temp_qty = 0;
        $ticket->save();

        $sevent = Sevent::find($seventId);
        
        return $this->paymentWithInvoice($invoice, $sevent->user_id, $gatewayId);
    }

    protected function paymentWithInvoice(Invoice $invoice, int $ownerId, int $gatewayId): array
    {
        $order = Payment::initOrder($invoice);

        if (null === $order) {
            return [];
        }

        $url = $invoice->toUrl();

        return Payment::placeOrder($order, $gatewayId, [
            'return_url' => $url,
            'cancel_url' => $url,
            'payee_id'   => $ownerId,
        ]);
    }

    public function saveQRCode($ticket)
    {
        $sevent = Sevent::find($ticket->sevent_id);
        $saleTicket = Ticket::find($ticket->ticket_id);
        $writer = new PngWriter();

        // Create QR code
        $qrCode = QrCode::create(__p('sevent::web.qr_code',[
                'number' => $ticket->number,
                'event_name' => $sevent->title,
                'ticket_name' => $saleTicket->title,
                'date' => Carbon::parse($sevent->start_date)
            ]))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $result = $writer->write($qrCode);
        $imagePath = tempnam(sys_get_temp_dir(), 'metafox') . $ticket->number.'_thumbnail.jpg';
        $result->saveToFile($imagePath);

        $image = upload()
                ->setStorage('photo')
                ->setPath('sevent')
                ->setThumbSizes(['300'])
                ->setItemType('photo')
                ->setUser(user())
                ->storeFile(UploadFile::pathToUploadedFile($imagePath));

        return $image->entityId();
    }

    public function updateSuccessPayment(int $id, ?string $transactionId = null): void
    {
        $status = 'completed';

        $invoice = $this->updatePaymentStatus($id, $status);

        if (null === $invoice) {
            return;
        }

        $invoice->fill([
            'paid_at' => $invoice->freshTimestamp(),
        ]);

        $invoice->saveQuietly();

        $transaction = resolve(InvoiceTransactionRepositoryInterface::class)->createTransaction([
            'invoice_id'      => $id,
            'status'          => $status,
            'price'           => $invoice->price,
            'currency_id'     => $invoice->currency_id,
            'transaction_id'  => $transactionId,
            'payment_gateway' => $invoice->payment_gateway,
        ]);

        if (null === $transaction) {
            return;
        }

        if (null === $invoice->user) {
            return;
        }

        if (null !== $invoice->sevent) {
            $sevent = resolve(SeventRepositoryInterface::class)->find($invoice->sevent_id);
            if ($invoice->ticket_id > 0) {
                $ticket = resolve(TicketRepositoryInterface::class)->find($invoice->ticket_id);
                $ticket->total_sales = $ticket->total_sales + $invoice->qty;
                $ticket->save();
                
                // add tickets for user
                for ($i = 0; $i < $invoice->qty; $i++) {
                    $userTicket = new UserTicket();
                    $userTicket->fill([
                        'sevent_id' => $invoice->sevent_id,
                        'user_type' => 'user',
                        'owner_id' => $invoice->user_id,
                        'owner_type' => 'user',
                        'user_id' => $invoice->user_id,
                        'number' => Str::random(6),
                        'paid_at' => Carbon::now()->toDateTimeString(),
                        'ticket_id' => $invoice->ticket_id
                    ]);

                    $userTicket->save();

                    // generate qr
                    $imageFileId = $this->saveQRCode($userTicket);
                    $userTicket->image_file_id = $imageFileId;
                    $userTicket->save();
                }
            }

            $alreadyAttend = Attend::where('user_id', '=', $invoice->user_id)
                ->where('sevent_id','=', $invoice->sevent_id)
                ->count();

            if ($alreadyAttend == 0) {
                $sevent->total_attending++;
                $sevent->save();

                // Attend user to event
                $newAttend = new Attend();
                $newAttend->fill([
                    'sevent_id' => $invoice->sevent_id,
                    'user_id'   => $invoice->user_id,
                    'type_id'   => 1
                ]);
                $newAttend->save();
            }

            if (null !== $invoice->sevent->user) {
                $this->toOwnerSuccessNotification($invoice->sevent->user, $transaction);
            }
        }

        $this->toSuccessNotification($invoice->user, $transaction);
    }

    public function updatePendingPayment(int $id, ?string $transactionId = null): void
    {
        $status = 'pending_payment';

        $invoice = $this->updatePaymentStatus($id, $status);

        if (null === $invoice) {
            return;
        }

        $transaction = resolve(InvoiceTransactionRepositoryInterface::class)->createTransaction([
            'invoice_id'      => $id,
            'status'          => $status,
            'price'           => $invoice->price,
            'currency_id'     => $invoice->currency_id,
            'transaction_id'  => $transactionId,
            'payment_gateway' => $invoice->payment_gateway,
        ]);

        if (null === $transaction) {
            return;
        }

        if (null === $invoice->user) {
            return;
        }

        $this->toPendingNotification($invoice->user, $transaction);
    }

    protected function updatePaymentStatus(int $id, string $status): ?Invoice
    {
        $invoice = $this->find($id);

        if ($invoice->status == $status) {
            return null;
        }

        $invoice->fill([
            'status' => $status,
        ]);

        $invoice->saveQuietly();

        return $invoice;
    }

    protected function toPendingNotification(User $user, InvoiceTransaction $transaction)
    {
        $params = [$user, new PaymentPendingNotification($transaction)];

        Notification::send(...$params);
    }

    protected function toSuccessNotification(User $user, InvoiceTransaction $transaction)
    {
        $params = [$user, new PaymentSuccessNotification($transaction)];

        Notification::send(...$params);
    }

    protected function toOwnerSuccessNotification(User $user, InvoiceTransaction $transaction)
    {
        $params = [$user, new OwnerPaymentSuccessNotification($transaction)];

        Notification::send(...$params);
    }

    public function viewInvoices(User $context, array $attributes = []): Paginator
    {
        policy_authorize(InvoicePolicy::class, 'viewAny', $context);

        $limit = Arr::get($attributes, 'limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = $this->buildQueryViewInvoices($context, $attributes);
        //print_r($query->toSql());die;
        return $query->simplePaginate($limit, ['sevent_invoices.*']);
    }

    private function buildQueryViewInvoices(User $context, array $attributes): Builder
    {
        $view      = Arr::get($attributes, 'view', ViewScope::VIEW_DEFAULT);
        $status    = Arr::get($attributes, 'status');
        $seventId = Arr::get($attributes, 'sevent_id');
        $dateFrom  = Arr::get($attributes, 'from');
        $dateTo    = Arr::get($attributes, 'to');

        $query = $this->getModel()
            ->newModelQuery()
            ->with(['sevent']);

        $viewScope = new ViewScope();
        $viewScope->setView($view)->setUserContext($context);
        $query->addScope($viewScope);

        if ($seventId) {
            $query->whereHas('sevent', function ($q) use ($seventId) {
                $q->where('sevents.id', '=', $seventId);
            });
        }

        if ($status and $status != 'all') {
            $query->where('sevent_invoices.status', $status);
        }

        if ($dateFrom) {
            $query->where('sevent_invoices.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('sevent_invoices.created_at', '<=', $dateTo);
        }

        $query->orderByRaw(DB::raw('
                CASE
                    WHEN sevent_invoices.paid_at IS NOT NULL THEN 1
                    ELSE 2
                END ASC
            ')->getValue(DB::getQueryGrammar()))
            ->orderByDesc('sevent_invoices.created_at');

        return $query;
    }

    public function viewInvoice(User $context, int $id): ?Invoice
    {
        $invoice = $this
            ->with(['transactions'])
            ->find($id);

        policy_authorize(InvoicePolicy::class, 'view', $context, $invoice);

        return $invoice;
    }

    public function changeInvoice(User $context, int $id): ?Invoice
    {
        return null;
    }

    public function repaymentInvoice(User $context, int $id, int $gatewayId): array
    {
        return [];
    }

    public function getTransactionTableFields(): array
    {
        return [
            [
                'label'  => __p('sevent::phrase.transaction_date'),
                'value'  => 'creation_date',
                'isDate' => true,
            ],
            [
                'label' => __p('sevent::phrase.amount'),
                'value' => 'price',
            ],
            [
                'label' => __p('sevent::phrase.payment_method'),
                'value' => 'payment_method',
            ],
            [
                'label' => __p('sevent::phrase.id'),
                'value' => 'transaction_id',
            ],
            [
                'label' => __p('sevent::phrase.payment_status_label'),
                'value' => 'status',
            ],
        ];
    }
}
