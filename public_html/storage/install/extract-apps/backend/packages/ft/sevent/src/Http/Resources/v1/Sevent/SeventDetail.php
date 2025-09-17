<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Sevent;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MetaFox\Core\Traits\HasDescriptionTrait;
use Foxexpert\Sevent\Models\Sevent;
use Foxexpert\Sevent\Models\Ticket;
use MetaFox\Platform\Facades\Settings;
use Foxexpert\Sevent\Models\Attend;
use MetaFox\Hashtag\Traits\HasHashtagTextTrait;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Facades\ResourceGate;
use Foxexpert\Sevent\Repositories\SeventFavouriteRepositoryInterface;
use MetaFox\Platform\Traits\Helpers\IsFriendTrait;
use MetaFox\Platform\Traits\Helpers\IsLikedTrait;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Traits\Http\Resources\HasFeedParam;
use MetaFox\Platform\Traits\Http\Resources\HasStatistic;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use Foxexpert\Sevent\Repositories\Eloquent\SeventRepository;
use Foxexpert\Sevent\Http\Controllers\Api\v1\SeventController;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Http\Resources\v1\User\UserItemCollection;
use Illuminate\Support\Carbon;

/**
 * Class SeventDetail.
 * @property Sevent $resource
 */
class SeventDetail extends JsonResource
{
    use HasExtra;
    use HasStatistic;
    use HasFeedParam;
    use IsLikedTrait;
    use IsFriendTrait;
    use HasHashtagTextTrait;

    /**
     * @return array<string, mixed>
     */
    public function getStatistic(): array
    {
        $reactItem = $this->resource->reactItem();
        $context  = user();
            
        $where = 'sevent_tickets.sevent_id='.$this->resource->entityId();
        if ($context->entityId() != $this->resource->user_id) {
            $where .= " AND sevent_tickets.expiry_date > '".Carbon::now()->format('Y-m-d H:i:s')."'
                AND (sevent_tickets.is_unlimited = 1 OR (sevent_tickets.qty > sevent_tickets.total_sales))";
        }

        $total_ticket = Ticket::whereRaw($where)->count();

        $totalAttending = Attend::where('sevent_id','=', $this->resource->entityId())
            ->where('type_id','=', 1)
            ->count();

        $totalInterested = Attend::where('sevent_id','=', $this->resource->entityId())
            ->where('type_id','=', 2)
            ->count();

        return [
            'total_like'       => $reactItem instanceof HasTotalLike ? $reactItem->total_like : 0,
            'total_view'       => $this->resource->total_view,
            'total_ticket'     => $total_ticket,
            'total_attending'  => $totalAttending,
            'total_interested' => $totalInterested,
            'total_share'      => $this->resource->total_share,
            'total_comment'    => $reactItem instanceof HasTotalComment ? $reactItem->total_comment : 0,
            'total_reply'      => $reactItem instanceof HasTotalCommentWithReply ? $reactItem->total_reply : 0,
            'total_attachment' => $this->resource->total_attachment,
        ];
    }

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
        $context    = user();
        $isDraft    = $this->resource->is_draft;
        $isApproved = $this->resource->is_approved;
        $isPending  = false;
        if (!$isDraft) {
            if (!$isApproved) {
                $isPending = true;
            }
        }
        $isFavourite = resolve(SeventFavouriteRepositoryInterface::class)
            ->favouriteExists($context, $this->resource->entityId());

        $shortDescription = $text  = '';
        if ($this->resource->seventText) {
            $shortDescription = parse_output()->getDescription($this->resource->seventText->text_parsed);
            $text             = $this->getTransformContent($this->resource->seventText->text_parsed);
            $text             = parse_output()->parse($text);
            $timeToRead = ceil(str_word_count(strip_tags($this->resource->seventText->text_parsed)) / 225);
        }

        $postOnOther   = $this->resource->userId() != $this->resource->ownerId();
        $ownerResource = null;

        if ($postOnOther && null !== $this->resource->ownerEntity) {
            $ownerResource = ResourceGate::user($this->resource->ownerEntity);
        }

        // recount status
        $status = resolve(SeventRepository::class)->getStatus($this->resource);
        $is_expiry = resolve(SeventRepository::class)->isExpiry($this->resource);

        // generate video iframe code
        $iframeVideo = '';
        if (!empty($this->resource->video)) {
            // check pattern
            if (preg_match('~uvideo/(\d+)~', $this->resource->video, $matches)) {
                $id = $matches[1] ?? '';
                $rootUrl = url('/');

                // fix for localhost
                if (url('/') == 'http://localhost')
                    $rootUrl = 'http://localhost:3000';

                if ($id > 0) 
                    $iframeVideo = '<iframe src="'.$rootUrl.'/uvideo/embed/'.$id.'" 
                    style="border:0;position:absolute;left:0;top:0;width:100%;height:100%" frameborder="0"></iframe>';
            } 
        }

        $userCurrency = app('currency')->getUserCurrencyId($context);
        
        $attend = Attend::where('sevent_id','=', $this->resource->entityId())
            ->where('user_id','=', $context->entityId())
            ->first();
        
        // find from ticket
        $where = 'sevent_tickets.sevent_id='.$this->resource->entityId();
        if ($context->entityId() != $this->resource->user_id) {
            $where .= " AND sevent_tickets.expiry_date >'".Carbon::now()->format('Y-m-d H:i:s')."'
                AND (sevent_tickets.is_unlimited = 1 OR (sevent_tickets.qty > sevent_tickets.total_sales))";
        }
        
        $ticket = Ticket::whereRaw($where)->orderBy('sevent_tickets.amount','asc')->first();

        $from = (!empty($ticket) and $ticket->amount > 0) ?
         app('currency')->getPriceFormatByCurrencyId($userCurrency, (float)$ticket->amount) 
         : 'free';

        return [
            'id'                => $this->resource->entityId(), 
            'status'            => $status,
            'from'              => $from,
            'userCurrency'      => $userCurrency,
            'iframeVideo'       => $iframeVideo,
            'is_expiry'         => $is_expiry,
            'attend'            => $attend ? $attend->type_id : 0,
            'module_name'       => $this->resource->entityType(),
            'is_favourite'      => $isFavourite,
            'start_date'        => $this->resource->start_date,
            'end_date'          => $this->resource->end_date,
            'resource_name'     => $this->resource->entityType(),
            'title'             => $this->resource->title,
            'lat' => $this->resource->location_latitude,
            'lng'=> $this->resource->location_longitude,
            'google_map_api_key' => Settings::get('core.google.google_map_api_key'),
            'title'             => $this->resource->title,
            'terms'             => $this->resource->terms,
            'is_online'         => $this->resource->is_online,
            'online_link'       => $this->resource->online_link,
            'expiry_date'       => $this->resource->expiry_date,
            'short_description' => $this->resource->short_description,
            'location_name'     => $this->resource->location_name,
            'description'       => $shortDescription,
            'module_id'         => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerType() : $this->resource->entityType(),
            'item_id'           => $this->resource->ownerId() != $this->resource->userId() ? $this->resource->ownerId() : 0,
            'is_approved'       => $isApproved,
            'is_sponsor'        => $this->resource->is_sponsor,
            'is_featured'       => $this->resource->is_featured,
            'is_liked'          => $this->isLike($context, $this->resource),
            'is_friend'         => $this->isFriend($context, $this->resource->user),
            'is_pending'        => $isPending,
            'is_draft'          => $isDraft,
            'is_saved'          => PolicyGate::check($this->resource->entityType(), 'isSavedItem', [$context, $this->resource]),
            'post_status'       => $this->resource->is_draft ? Sevent::RADIO_STATUS_DRAFT : Sevent::RADIO_STATUS_PUBLIC,
            'text'              => $text,
            'attach_photos'        => $this->getAttachedPhotos(),
            'image'             => $this->resource->image,
            'statistic'         => $this->getStatistic(),
            'privacy'           => $this->resource->privacy,
            'user'              => ResourceGate::user($this->resource->userEntity),
            'owner'             => ResourceGate::user($this->resource->ownerEntity),
            'owner_type_name'   => __p_type_key($this->resource->ownerType()),
            'categories'        => ResourceGate::embeds($this->resource->activeCategories, false),
            'tags'              => $this->resource->tags,
            'attachments'       => ResourceGate::items($this->resource->attachments, false),
            'is_sponsored_feed' => $this->resource->sponsor_in_feed,
            'creation_date'     => $this->resource->created_at,
            'modification_date' => $this->resource->updated_at,
            'link'              => $this->resource->toLink(),
            'url'               => $this->resource->toUrl(),
            'extra'             => $this->getExtra(),
            'feed_param'        => $this->getFeedParams(),
            'info'              => 'added_a_sevent',
            'location'          => $this->resource->toLocationObject(),

            'is_host'           => $this->resource->is_host,
            'host'              => $this->resource->host,
            'host_title'             => $this->resource->host_title,
            'host_contact'             => $this->resource->host_contact,
            'host_website'             => $this->resource->host_website,
            'host_facebook'             => $this->resource->host_facebook,
            'host_description'             => $this->resource->host_description
        ];
    }

    protected function getAttachedPhotos()
    {
        $attachedPhotos = null;

        if ($this->resource->photos->count()) {
            $attachedPhotos = $this->resource->photos->map(function ($photo) {
                return ResourceGate::asItem($photo, null);
            });
        }

        return $attachedPhotos;
    }
}