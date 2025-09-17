<?php

namespace Foxexpert\Sevent\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Metafox\Search\Models\Search;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Policies\CategoryPolicy;
use Foxexpert\Sevent\Repositories\TicketRepositoryInterface;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\ViewScope;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use Foxexpert\Sevent\Repositories\ImageRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class TicketRepository.
 * @property Ticket $model
 * @method   Ticket getModel()
 * @method   Ticket find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @ignore
 * @codeCoverageIgnore
 */
class TicketRepository extends AbstractRepository implements TicketRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Ticket::class;
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    public function createTicket(User $context, User $owner, array $attributes): Ticket
    {
        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType()
        ]);

        $attributes['title'] = $this->cleanTitle($attributes['title']);
        $attributes['image_file_id'] = upload()->getFileId($attributes['temp_file'], true);
        $ticket = new Ticket($attributes);

        $ticket->save();

        $ticket->refresh();

        return $ticket;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws AuthenticationException
     */
    public function updateTicket(User $context, int $id, array $attributes): Ticket
    {
        $ticket = $this->withUserMorphTypeActiveScope()->find($id);

        $removeImage = Arr::get($attributes, 'remove_image', 0);

        if (isset($attributes['title'])) {
            $attributes['title'] = $this->cleanTitle($attributes['title']);
        }

        if ($removeImage) {
            $image = $ticket->image_file_id;
            app('storage')->deleteFile($image, null);
            $attributes['image_file_id'] = null;
        }

        if ($attributes['temp_file'] > 0) {
            $attributes['image_file_id'] = upload()->getFileId($attributes['temp_file'], true);
        }
        
        $ticket->fill($attributes);

        $ticket->save();

        $ticket->refresh();

        return $ticket;
    }

    public function deleteTicket(User $user, $id): int
    {
        $resource = $this->withUserMorphTypeActiveScope()->find($id);

        return $this->delete($id);
    }

    public function viewTickets(User $context, User $owner, array $attributes): Paginator
    {
        $limit = $attributes['limit'];
        $this->withUserMorphTypeActiveScope();
        $query = $this->getModel()->newQuery();
        $seventId = $attributes['sevent_id'] ?? 0;
        
        // clean results
        if (empty($seventId)) $query->where('sevent_id','<',0);

        $where = 'sevent_tickets.sevent_id='.$seventId;
        if ($attributes['sort'] == 'amount')
            $query->orderBy('amount', 'asc');
        
        return $query->whereRaw($where)->orderBy('sevent_tickets.amount', 'asc')
            ->simplePaginate($limit, ['sevent_tickets.*']);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    
}
