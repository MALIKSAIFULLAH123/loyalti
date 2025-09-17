<?php

namespace MetaFox\Featured\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Http\Requests\v1\Item\IndexRequest;
use MetaFox\Featured\Http\Requests\v1\Item\StoreRequest;
use MetaFox\Featured\Http\Resources\v1\Invoice\PaymentInvoiceForm;
use MetaFox\Featured\Http\Resources\v1\Item\CreateItemForm;
use MetaFox\Featured\Http\Resources\v1\Item\CreateItemMobileForm;
use MetaFox\Featured\Http\Resources\v1\Item\ItemDetail as Detail;
use MetaFox\Featured\Http\Resources\v1\Item\ItemItemCollection as ItemCollection;
use MetaFox\Featured\Models\Invoice;
use MetaFox\Featured\Models\Item;
use MetaFox\Featured\Policies\ItemPolicy;
use MetaFox\Featured\Repositories\InvoiceRepositoryInterface;
use MetaFox\Featured\Repositories\ItemRepositoryInterface;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxPrivacy;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Featured\Http\Controllers\Api\ItemController::$controllers;
 */

/**
 * Class ItemController
 * @codeCoverageIgnore
 * @ignore
 */
class ItemController extends ApiController
{
    /**
     * @var ItemRepositoryInterface
     */
    private ItemRepositoryInterface $repository;

    /**
     * ItemController Constructor
     *
     * @param ItemRepositoryInterface $repository
     */
    public function __construct(ItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item
     *
     * @param IndexRequest $request
     *
     * @return mixed
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        $data = $this->repository->viewItems($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $item = Feature::morphItemFromEntityType(Arr::get($params, 'item_type'), Arr::get($params, 'item_id'));

        policy_authorize(ItemPolicy::class, 'create', $context, $item);

        $data = $this->repository->createItem($context, $item, $params);

        $message = __p('featured::phrase.your_featured_item_has_successfully_been_submitted');

        if ($data->is_running) {
            $message = __p('featured::phrase.your_featured_item_is_now_running');
        }

        return $this->success(new Detail($data), [
            'nextAction' => [
                'type'    => 'navigate',
                'payload' => [
                    'url'     => '/featured',
                    'replace' => true,
                ],
            ],
        ], $message);
    }

    /**
     * @param string $itemType
     * @param int    $itemId
     *
     * @urlParam   item_id int The ID of the item.
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function getCreateForm(string $itemType, int $itemId): JsonResponse
    {
        $context = user();

        $item = Feature::morphItemFromEntityType($itemType, $itemId);

        if ($item instanceof HasPrivacy && $item->privacy == MetaFoxPrivacy::ONLY_ME) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_privacy_is_set_to_only_me'));
        }

        policy_authorize(ItemPolicy::class, 'create', $context, $item);

        $form = match (MetaFox::isMobile()) {
            true    => new CreateItemMobileForm($item),
            default => new CreateItemForm($item),
        };

        return $this->success($form);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();

        $featuredItem = $this->repository->find($id);

        policy_authorize(ItemPolicy::class, 'delete', $context, $featuredItem);

        $this->repository->deleteItem($featuredItem);

        return $this->success([], [], __p('featured::phrase.your_featured_item_was_deleted_successfully'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function cancel(int $id): JsonResponse
    {
        $context = user();

        /**
         * @var Item $featuredItem
         */
        $featuredItem = $this->repository->find($id);

        policy_authorize(ItemPolicy::class, 'cancel', $context, $featuredItem);

        $this->repository->markItemCancelled($context, $featuredItem);

        $featuredItem->refresh();

        return $this->success(new Detail($featuredItem), [], __p('featured::phrase.your_featured_item_was_cancelled_successfully'));
    }

    public function getPaymentForm(int $id): JsonResponse
    {
        $context = user();

        $item = $this->repository->find($id);

        policy_authorize(ItemPolicy::class, 'payment', $context, $item);

        /**
         * @var Invoice $invoice
         */
        $invoice = resolve(InvoiceRepositoryInterface::class)->getUnpaidInvoiceForItem($context, $item);

        if ($invoice->is_completed) {
            return $this->success([
                'url' => $invoice->toLink(),
            ], [], __p('featured::phrase.your_featured_item_was_paid_successfully'));
        }

        $form = resolve(PaymentInvoiceForm::class);

        app()->call([$form, 'boot'], ['id' => $invoice->entityId()]);

        return $this->success($form, $form->getFormMeta());
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        $context = user();

        policy_authorize(ItemPolicy::class, 'view', $context, $item);

        return $this->success(new Detail($item));
    }
}
