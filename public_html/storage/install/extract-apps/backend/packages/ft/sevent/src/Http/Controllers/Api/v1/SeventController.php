<?php

namespace Foxexpert\Sevent\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Foxexpert\Sevent\Models\Attend;
use Foxexpert\Sevent\Repositories\Eloquent\InvoiceRepository;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\CreateFormRequest;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\IndexRequest;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\PatchRequest;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\StoreRequest;
use Foxexpert\Sevent\Http\Requests\v1\Sevent\UpdateRequest;
use Foxexpert\Sevent\Repositories\SeventFavouriteRepositoryInterface;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\SeventDetail;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\SeventItemCollection;
use Foxexpert\Sevent\Http\Resources\v1\UserTicket\UserTicketItemCollection;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\TagItemCollection;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\SearchSeventForm as SearchForm;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\StoreSeventForm;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\UpdateSeventForm;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\Ticket;
use Foxexpert\Sevent\Models\UserTicket;
use Foxexpert\Sevent\Models\Invoice;
use Metafox\User\Models\User;
use Foxexpert\Sevent\Models\SeventTagData;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Support\Facades\UserEntity;
use Foxexpert\Sevent\Http\Resources\v1\Sevent\MemberItemCollection;
use MetaFox\User\Support\Facades\UserPrivacy;
use Foxexpert\Sevent\Database\Seeders\PackageSeeder;
use MetaFox\Page\Models\IntegratedModule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use MetaFox\Platform\Facades\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use MetaFox\Platform\Facades\ResourceGate;

/**
 * Class SeventController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group sevent
 */
class SeventController extends ApiController
{
    /**
     * @var SeventRepositoryInterface
     */
    private SeventRepositoryInterface $repository;

    /**
     * @param SeventRepositoryInterface $repository
     */
    public function __construct(SeventRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    

    /**
     * Browse sevent.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        //resolve(PackageSeeder::class)->run();
        //die;
        /*$context = user();
        $categories = resolve(CategoryRepositoryInterface::class)->viewForAdmin($context, []);
        $categoryOptions = [];
        foreach ($categories as $category) {
            $categoryOptions[] = [
                'label' => __p($category->name), 
                'value' => $category->id
            ];
        }
        var_dump($categoryOptions);
        die;*/
        $params  = $request->validated();
        $context = user();
        $owner   = $context;
        $view    = Arr::get($params, 'view');
        $limit   = Arr::get($params, 'limit', 4);

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;
            policy_authorize(SeventPolicy::class, 'viewOnProfilePage', $context, $owner);
        }

        policy_authorize(SeventPolicy::class, 'viewAny', $context, $owner);
    
        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->findSponsor($limit),
            default              => $this->repository->viewSevents($context, $owner, $params),
        };

        return $this->success(new SeventItemCollection($data));
    }

    /**
     * Create sevent.
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
        
        $sevent = $this->repository->createSevent($context, $owner, $params);

        $message = __p('sevent::phrase.sevent_published_successfully');

        if (!$sevent->isApproved()) {
            $message = __p('core::phrase.thanks_for_your_item_for_approval');
        }
        
        $ownerPendingMessage = $sevent->getOwnerPendingMessage();

        if (null !== $ownerPendingMessage) {
            $message = $ownerPendingMessage;
        }

        if ($params['is_draft']) {
            $message = __p('sevent::phrase.already_saved_sevent_as_draft');
        }

        return $this->success(new SeventDetail($sevent), [], $message);
    }

    /**
     * View sevent.
     *
     * @param int $id
     *
     * @return SeventDetail
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show(int $id)
    {     
        //print_r(\DateTimeZone::listIdentifiers());die;
        $sevent = $this->repository->viewSevent(user(), $id);

        return new SeventDetail($sevent);
    }

    /**
     * Update sevent.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function update(StoreRequest $request, int $id): JsonResponse
    {
        $params   = $request->validated();
        $sevent     = $this->repository->updateSevent(user(), $id, $params);
        $response = new SeventDetail($sevent);
        $message  = __p('sevent::phrase.sevent_was_updated_successfully');

        $isPublished = true;
        if (isset($params['published'])) {
            $isPublished = $params['published'];
        }

        if (!$isPublished) {
            if (!$params['is_draft']) {
                $message = __p('sevent::phrase.sevent_published_successfully');
            }
        }

        return $this->success($response, [], $message);
    }

    /**
     * Delete sevent.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $context = user();
        $this->repository->deleteSevent($context, $id);

        $message = __p('sevent::phrase.sevent_was_deleted_successfully');

        return $this->success([
            'id' => $id,
        ], [], $message);
    }

    /**
     * Patch update sevent.
     *
     * @param  PatchRequest $request
     * @param  int          $id
     * @return JsonResponse
     */
    public function patch(PatchRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $sevent   = $this->repository->find($id);

        return $this->success(new SeventDetail($sevent));
    }

    /**
     * Sponsor sevent.
     *
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();

        $sponsor = $params['sponsor'];

        $this->repository->sponsor(user(), $id, $sponsor);

        $sevent = $this->repository->find($id);

        $isSponsor = (bool) $sponsor;

        $isPendingSponsor = $isSponsor && !$sevent->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');

        $message = __p($message, ['resource_name' => __p('sevent::phrase.sevent')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new SeventDetail($sevent), [], $message);
    }

    /**
     * Feature sevent.
     *
     * @param FeatureRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function feature(FeatureRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $feature = $params['feature'];
        $this->repository->feature(user(), $id, $feature);

        $message = __p('sevent::phrase.sevent_featured_successfully');
        if (!$feature) {
            $message = __p('sevent::phrase.sevent_unfeatured_successfully');
        }

        return $this->success([
            'id'          => $id,
            'is_featured' => (int) $feature,
        ], [], $message);
    }
    
    /**
     * Approve sevent.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $resource = $this->repository->approve(user(), $id);

        // @todo recheck response.
        return $this->success(new SeventDetail($resource), [], __p('sevent::phrase.sevent_has_been_approved'));
    }

    /**
     * Publish sevent.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function publish(int $id): JsonResponse
    {
        $context = user();
        $sevent    = $this->repository->publish($context, $id);

        $message = $sevent->isApproved()
            ? __p('sevent::phrase.sevent_published_successfully')
            : __p('sevent::phrase.thank_you_for_your_item_it_s_been_submitted_to_admins_for_approval');

        return $this->success(new SeventDetail($sevent), [], $message);
    }

    /**
     * @param CreateFormRequest $request
     *
     * @return AbstractForm
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function formStore(CreateFormRequest $request): AbstractForm
    {
        $sevent    = new Sevent();
        $context = user();

        $data           = $request->validated();
        $sevent->owner_id = $data['owner_id'];

        policy_authorize(SeventPolicy::class, 'create', $context);

        return new StoreSeventForm($sevent);
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

        $sevent = $this->repository->find($id);
        policy_authorize(SeventPolicy::class, 'update', $context, $sevent);

        return $this->success(new UpdateSeventForm($sevent), [], '');
    }

    /**
     * Sponsor sevent in feed.
     *
     * @param SponsorInFeedRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function sponsorInFeed(SponsorInFeedRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();

        $sponsor = $params['sponsor'];

        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $isSponsor        = (bool) $sponsor;
        $sevent             = $this->repository->find($id);
        $isPendingSponsor = $isSponsor && !$sevent->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        $message = __p($message, ['resource_name' => __p('sevent::phrase.sevent')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new SeventDetail($sevent), [], $message);
    }

    public function favourite(int $id): JsonResponse
    {
        $sevent = $this->repository->find($id);

        $context = user();

        policy_authorize(SeventPolicy::class, 'view', $context, $sevent);

        resolve(SeventFavouriteRepositoryInterface::class)->updateFavourite($context, $id);

        return $this->success([
            'is_favourite' => resolve(SeventFavouriteRepositoryInterface::class)->favouriteExists($context, $id)
        ]);
    }

    public function attend(IndexRequest $request)
    {
        $params  = $request->validated();
        $context = user();
        $sevent = $this->repository->find((int)$params['id']);
        if (!$sevent->entityId() or !$context->entityId()) return;

        $alreadyAttend = Attend::where('user_id', '=', $context->entityId())
            ->where('sevent_id','=', $sevent->entityId())
            ->first();
            
        if (!$alreadyAttend) {
            $sevent->total_attending++;
            $sevent->save();

            // Attend user to event
            $newAttend = new Attend();
            $newAttend->fill([
                'sevent_id' => $sevent->entityId(),
                'user_id'   => $context->entityId(),
                'type_id'   => $params['type_id']
            ]);
            $newAttend->save();
        } else {
            // Update
            $alreadyAttend->type_id = $params['type_id'];
            $alreadyAttend->save();
        }

        return $this->success([]);
    }

    public function free(IndexRequest $request)
    {
        $params  = $request->validated();
        $context = user();
        $ticket = Ticket::find($params['id']);
        $sevent = $this->repository->find($ticket->sevent_id);
        $qty = $params['qty'];
        
        $ticket->total_sales = $ticket->total_sales + $qty;
        $ticket->save();

        // add tickets for user
        for ($i = 0; $i < $qty; $i++) {
            $userTicket = new UserTicket();
            $userTicket->fill([
                'sevent_id' => $ticket->sevent_id,
                'user_type' => 'user',
                'owner_id' => $context->entityId(),
                'owner_type' => 'user',
                'user_id' => $context->entityId(),
                'number' => Str::random(6),
                'paid_at' => Carbon::now()->toDateTimeString(),
                'ticket_id' => $ticket->entityId()
            ]);
            $userTicket->save();

            // generate qr
            $imageFileId = resolve(InvoiceRepository::class)->saveQRCode($userTicket);
            $userTicket->image_file_id = $imageFileId;
            $userTicket->save();
        }

        $alreadyAttend = Attend::where('user_id', '=', $context->entityId())
            ->where('sevent_id','=', $ticket->sevent_id)
            ->count();

        if ($alreadyAttend == 0) {
            $sevent->total_attending++;
            $sevent->save();

            // Attend user to event
            $newAttend = new Attend();
            $newAttend->fill([
                'sevent_id' => $ticket->sevent_id,
                'user_id'   => $context->entityId(),
                'type_id'   => 1
            ]);

            $newAttend->save();
        }

        return $this->success([]);
    }

    public function massEmail(IndexRequest $request, $id)
    {
        $params  = $request->validated();
        $context = user();
        $this->repository->massEmail($context, $id, $params);

        return $this->success([], [], __p('sevent::phrase.email_sent_successfully'));
    }
    
    /**
     * Get search form.
     *
     * @return AbstractForm
     * @todo Need working with policy + repository later
     */
    public function searchForm(): AbstractForm
    {
        return new SearchForm(null);
    }

    public function setupQty(IndexRequest $request)
    {
        $params  = $request->validated();
        $context = user();
        $ticketId = $params['ticket_id'];
        $qty = $params['qty'];

        $ticket = Ticket::find($ticketId);
        $ticket->temp_qty = $qty;
        $ticket->save();
        
        return $this->success([], [] ,'');
    }

    public function getCategories(): JsonResponse
    {
        $categories = resolve(CategoryRepositoryInterface::class)->viewForAdmin(user(), [
            'is_active' => 1
        ]);
        $objectArray = [];
        $objectArray[] = (object) [
            'label' => __p('sevent::web.all_categories'), 
            'id' => 0, 
            "module_name" => "sevent",
            "resource_name" => "sevent", 
            "module_id"=>"sevent"
        ];
        foreach ($categories as $category) {
            $object = (object) [
                'label' => __p($category->name), 
                'id' => $category->id, 
                "module_name" => "sevent",
                "resource_name" => "sevent", 
                "module_id"=>"sevent"
            ];
            $objectArray[] = $object;
        }
        
        return $this->success($objectArray);
    }

    public function download(int $id)
    {
        $context = user();
        $userTicket = UserTicket::find($id);
        if ($userTicket->image_file_id > 0) {
            $pdfFile = upload()->getFile($userTicket->image_file_id, true);
            $qrCode = Storage::url($pdfFile->path);
        }
        $sevent = Sevent::find($userTicket->sevent_id);
        $ticket = Ticket::find($userTicket->ticket_id);
       
        if (!$userTicket or !$sevent) return;
        
         // Create your HTML content
         $html = '
         <!DOCTYPE html>
         <html lang="en">
         <head>
             <meta charset="UTF-8">
             <title>'.$sevent->title.'</title>
             <style>
                 /* Add any styles needed for the PDF */
                 body {
                     font-family: Arial, sans-serif;
                     padding: 20px;
                 }
                 table tr td {
                    padding: 8px 56px 8px 0;    
                }
             </style>
         </head>
         <body><div style="display:flex;justify-content:space-between;gap: 35px;">';
         
        
         $html .= '<div style="flex-grow:1">';
         if (!empty($qrCode)) {
            $html .= '<img src="' . $qrCode. '" style="max-width:150px;height:150px;" />';
        }
         // Add the rest of the content
         $html .= '
             <h1>' . $sevent->title . '</h1>
             <p>' . $sevent->short_description . '</p>
             <table>
             <tr>
        ';
        if (!$sevent->is_online) {
            $html .= ' <td colspan="2"><h3>'.__p('sevent::web.location').':</h3>'.$sevent->location_name.'</td>';
        } else {
            $html .= '<td colspan="2"><h3>'.__p('sevent::web.registration_link').':</h3>'.$sevent->online_link.'</td>';
        }
        $html .= '</tr><tr>';
        $html .= ' <td><h3>'.__p('sevent::web.event_date').':</h3>'.
        Carbon::parse($sevent->start_date)->toDateTimeString().'</td>';

        $html .= '<td><h3>'.__p('sevent::web.ticket_number').':</h3>'.
        '#'.$userTicket->number.'</td>';

        $html .= '<tr>
        <td><h3>'.__p('sevent::web.ticket').':</h3>'.
        $ticket->title.'</td>';

        $html .= '
            <td><h3>'.__p('sevent::web.paid_at').':</h3>'.
            Carbon::parse($userTicket->paid_at)->toDateTimeString().'</td></tr></table>';
       
        if ($sevent->terms) {
            $html .= ' <h3>'.__p('sevent::web.terms').'</h3>
            <p>' . $sevent->terms . '</p>';
        }

        $html .= '</div>';
        
        $html .='
         </body>
         </html>';

         $pdf = Pdf::loadHTML($html);
         
         return $pdf->download($sevent->title.'.pdf');
    }

    public function myTickets(IndexRequest $request): JsonResponse
    {
        $context = user();
        $query = UserTicket::where('user_id','=', $context->entityId())
            ->orderBy('paid_at', 'desc');

        $attenders = $query->simplePaginate();

        return $this->success(new UserTicketItemCollection($attenders));
    }

    public function getAttending(IndexRequest $request): JsonResponse
    {
        $columns = \Schema::getColumnListing('users');      
        $params  = $request->validated();
        $eventId = $params['sevent_id'];

        $query = User::select('users.*', DB::raw('sevent_attends.created_at as attend_date'))
            ->join('sevent_attends', 'users.id', '=', 'sevent_attends.user_id')
            ->where('sevent_attends.sevent_id', '=', $eventId)
            ->where('sevent_attends.type_id', '=', 1)
            ->groupBy('sevent_attends.created_at')
            ->groupBy('sevent_attends.type_id')
            ->orderByDesc('sevent_attends.created_at');

        foreach ($columns as $column) {
            $query->groupBy("users.$column");
        }

        $attenders = $query->simplePaginate();

        return $this->success(new MemberItemCollection($attenders));
    }

    public function getInterested(IndexRequest $request): JsonResponse
    {
        $columns = \Schema::getColumnListing('users');      
        $params  = $request->validated();
        $eventId = $params['sevent_id'];

        $query = User::select('users.*', DB::raw('sevent_attends.created_at as attend_date'))
            ->join('sevent_attends', 'users.id', '=', 'sevent_attends.user_id')
            ->where('sevent_attends.sevent_id', '=', $eventId)
            ->where('sevent_attends.type_id', '=', 2)
            ->groupBy('attend_date')
            ->groupBy('sevent_attends.type_id')
            ->orderByDesc('sevent_attends.created_at');

        foreach ($columns as $column) {
            $query->groupBy("users.$column");
        }

        $attenders = $query->simplePaginate();

        return $this->success(new MemberItemCollection($attenders));
    }

    public function getTopics(): JsonResponse
    {
        $blogs = resolve(Sevent::class)->newQuery()
            ->where('tags', '<>', '')
            ->where('is_approved', '=', 1)
            ->where('is_draft', '=', 0)
            ->inRandomOrder()
            ->limit(20)
            ->get();

        $limit = 20;
        $i = 0;
        $tagsArray = [];
        $objectArray = [];
        if (!empty($blogs)) {
            foreach ($blogs as $blog) {
                foreach ($blog->tags as $tag) {
                    if (in_array($tag, $tagsArray) === false and $i < $limit) { 
                        $object = (object) ['tag' => $tag, 'id' => $i+1, "module_name" => "sevent",
                        "resource_name" => "sevent", "module_id"=>"sevent"];
                        $objectArray[] = $object;
                        $tagsArray[] = $tag;
                        $i++;
                    }
                }
            }
        }
        
        return $this->success($objectArray);

    }
}
