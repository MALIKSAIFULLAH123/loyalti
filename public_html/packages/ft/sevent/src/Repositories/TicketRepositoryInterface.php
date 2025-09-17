<?php

namespace Foxexpert\Sevent\Repositories;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Foxexpert\Sevent\Models\Ticket;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Support\Repository\Contracts\HasFeature;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsor;
use MetaFox\Platform\Support\Repository\Contracts\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Interface TicketRepositoryInterface.
 * @method Ticket find($id, $columns = ['*'])
 * @method Ticket getModel()
 *
 * @mixin CollectTotalItemStatTrait
 * @mixin UserMorphTrait
 */
interface TicketRepositoryInterface extends HasSponsor, HasFeature, HasSponsorInFeed
{
    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Paginator
     * @throws AuthorizationException
     */
    public function viewTickets(User $context, User $owner, array $attributes): Paginator;
    /**
     * Create a ticket.
     *
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Ticket
     * @throws AuthorizationException
     * @see StoreBlockLayoutRequest
     */
    public function createTicket(User $context, User $owner, array $attributes): Ticket;
    
    /**
     * Update a ticket.
     *
     * @param User                 $context
     * @param int                  $id
     * @param array<string, mixed> $attributes
     *
     * @return Ticket
     * @throws AuthorizationException
     */
    public function updateTicket(User $context, int $id, array $attributes): Ticket;

    /**
     * Delete a ticket.
     *
     * @param User $user
     * @param int  $id
     *
     * @return int
     * @throws AuthorizationException
     */
    public function deleteTicket(User $user, int $id): int;
}
