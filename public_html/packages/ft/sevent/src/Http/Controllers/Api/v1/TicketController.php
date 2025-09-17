<?php

namespace Foxexpert\Sevent\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Foxexpert\Sevent\Http\Requests\v1\Ticket\CreateFormRequest;
use Foxexpert\Sevent\Http\Requests\v1\Ticket\IndexRequest;
use Foxexpert\Sevent\Http\Requests\v1\Ticket\PatchRequest;
use Foxexpert\Sevent\Http\Requests\v1\Ticket\StoreRequest;
use Foxexpert\Sevent\Http\Requests\v1\Ticket\UpdateRequest;

use Foxexpert\Sevent\Http\Resources\v1\Ticket\TicketDetail;
use Foxexpert\Sevent\Http\Resources\v1\Ticket\TicketItem;
use Foxexpert\Sevent\Http\Resources\v1\Ticket\TicketItemCollection;
use Foxexpert\Sevent\Http\Resources\v1\Ticket\TagItemCollection;
use Foxexpert\Sevent\Http\Resources\v1\Ticket\SearchTicketForm as SearchForm;
use Foxexpert\Sevent\Http\Resources\v1\Ticket\StoreTicketForm;
use Foxexpert\Sevent\Http\Resources\v1\Ticket\UpdateTicketForm;
use Foxexpert\Sevent\Models\Ticket;
use Metafox\User\Models\User;
use Foxexpert\Sevent\Models\SeventTagData;
use Foxexpert\Sevent\Repositories\TicketRepositoryInterface;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;
use Foxexpert\Sevent\Http\Resources\v1\Ticket\MemberItemCollection;
use MetaFox\User\Support\Facades\UserPrivacy;
use Foxexpert\Sevent\Database\Seeders\PackageSeeder;
use MetaFox\Page\Models\IntegratedModule;
use Illuminate\Support\Facades\DB;
/**
 * Class TicketController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group Ticket
 */
class TicketController extends ApiController
{
    /**
     * @var TicketRepositoryInterface
     */
    private TicketRepositoryInterface $repository;

    /**
     * @param TicketRepositoryInterface $repository
     */
    public function __construct(TicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse Ticket.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $owner   = $context;
        $view    = Arr::get($params, 'view');
        $limit   = Arr::get($params, 'limit', 4);

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;
        }

        $data =  $this->repository->viewTickets($context, $owner, $params);

        return $this->success(new TicketItemCollection($data));
    }

    /**
     * Create ticket.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws PermissionDeniedException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $context = $owner = user();

        $params = $request->validated();
        
        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }
        
        $ticket = $this->repository->createTicket($context, $owner, $params);
        $response = new TicketItem($ticket);
        $message = __p('sevent::phrase.ticket_published_successfully');

        return $this->success($response, [], $message);
    }

    /**
     * Update ticket.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params   = $request->validated();
        $ticket     = $this->repository->updateTicket(user(), $id, $params);
        $response = new TicketItem($ticket);
        $message  = __p('sevent::phrase.ticket_was_updated_successfully');

        return $this->success($response, [], $message);
    }

    /**
     * Delete ticket.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();
        $this->repository->deleteTicket($context, $id);

        $message = __p('sevent::phrase.ticket_was_deleted_successfully');

        return $this->success([
            'id' => $id,
        ], [], $message);
    }

    /**
     * Patch update ticket.
     *
     * @param  PatchRequest $request
     * @param  int          $id
     * @return JsonResponse
     */
    public function patch(PatchRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $ticket   = $this->repository->find($id);

        return $this->success(new TicketItem($ticket));
    }

    /**
     * @param CreateFormRequest $request
     *
     * @return AbstractForm
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function formStore(CreateFormRequest $request)
    {
        $ticket    = new Ticket();
        $context = user();

        $data           = $request->validated();
        $ticket->owner_id = $data['owner_id'];

        return new StoreTicketForm($ticket);
    }

    /**
     * @param CreateFormRequest $request
     * @param int               $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function formUpdate(CreateFormRequest $request, int $id): JsonResponse
    {
        $context = user();

        $ticket = $this->repository->find($id);
        return $this->success(new UpdateTicketForm($ticket), [], '');
    }
}
