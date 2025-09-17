<?php

namespace Foxexpert\Sevent\Repositories\Eloquent;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Metafox\Search\Models\Search;
use Foxexpert\Sevent\Models\Sevent;
use MetaFox\User\Models\User as UserModel;
use Foxexpert\Sevent\Policies\SeventPolicy;
use Foxexpert\Sevent\Policies\CategoryPolicy;
use Foxexpert\Sevent\Repositories\SeventRepositoryInterface;
use Foxexpert\Sevent\Repositories\CategoryRepositoryInterface;
use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\ViewScope;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use Foxexpert\Sevent\Repositories\ImageRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\HasFeature;
use Foxexpert\Sevent\Mails\SeventMail;
use Foxexpert\Sevent\Models\Ticket;
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
use Foxexpert\Sevent\Support\Browse\Scopes\Sevent\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\BoundsScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class SeventRepository.
 * @property Sevent $model
 * @method   Sevent getModel()
 * @method   Sevent find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @ignore
 * @codeCoverageIgnore
 */
class SeventRepository extends AbstractRepository implements SeventRepositoryInterface
{
    use HasSponsor;
    use HasFeatured;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Sevent::class;
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    public function isExpiry($sevent)
    {
        if ($sevent['end_date'] < Carbon::now()->format('Y-m-d H:i:s')) {
            return true;
        }
        
        return false;
    }
    
    public function isTicketExpiry($ticket)
    {
        if ($ticket['expiry_date'] < Carbon::now()->format('Y-m-d H:i:s')) {
            return true;
        }
        
        return false;
    }

    public function getStatus($sevent) 
    {
        // check draft
        if ($sevent['is_draft'] == 1) {
            return 'draft';
        }

        // check pending
        if ($sevent['is_approved'] == 0) {
            return 'pending';
        }

        // check ongoing
        if ($sevent['start_date'] < Carbon::now()->format('Y-m-d H:i:s') 
            and $sevent['end_date'] > Carbon::now()->format('Y-m-d H:i:s')) {
            return 'ongoing';
        }
        
        // check live now
        if ($sevent['start_date'] > Carbon::now()->format('Y-m-d H:i:s')) {
            return 'upcoming';
        }

        // check expiry
        if ($sevent['end_date'] < Carbon::now()->format('Y-m-d H:i:s')) {
            return 'past';
        }
    }

    public function createSevent(User $context, User $owner, array $attributes): Sevent
    {
        policy_authorize(SeventPolicy::class, 'create', $context, $owner);

        $attributes = array_merge($attributes, [
            'user_id'     => $context->entityId(),
            'user_type'   => $context->entityType(),
            'owner_id'    => $owner->entityId(),
            'owner_type'  => $owner->entityType(),
            'module_id'   => Sevent::ENTITY_TYPE,
            'is_approved' => (int)policy_check(SeventPolicy::class, 'autoApprove', $context, $owner),
        ]);

        $attributes['title'] = $this->cleanTitle($attributes['title']);
        $attributes['image_file_id'] = upload()->getFileId($attributes['temp_file'], true);
        $attributes['host_image_file_id'] = upload()->getFileId($attributes['host_temp_file'], true);
        $sevent = new Sevent($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $sevent->setPrivacyListAttribute($attributes['list']);
        }

        $sevent->save();
        resolve(AttachmentRepositoryInterface::class)
            ->updateItemId($attributes['attachments'], $sevent);

        $this->handleAttachedPhotos($context, $sevent, Arr::get($attributes, 'attached_photos'), false);

        $sevent->refresh();

        return $sevent;
    }

    protected function handleAttachedPhotos(
        User    $context,
        Sevent $sevent,
        ?array  $attachedPhotos,
        bool    $isUpdated = true
    ): void
    {
        resolve(ImageRepositoryInterface::class)->updateImages(
            $context,
            $sevent->entityId(),
            $attachedPhotos,
            $isUpdated
        );
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws AuthenticationException
     */
    public function updateSevent(User $context, int $id, array $attributes): Sevent
    {
         //Add to search
         $aSearchData = [
            'item_id' => $id,
            'user_id' => $context->entityId(),
            'user_type' => 'user',
            'owner_id' => $context->entityId(),
            'item_type' => 'sevent',
            'privacy' => 0,
            'title' => $attributes['title'],
            'owner_type' => 'user',
            'text' => '',
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ];
        
        Search::query()->insert($aSearchData);


        $sevent = $this->withUserMorphTypeActiveScope()->find($id);

        $removeImage = Arr::get($attributes, 'remove_image', 0);

        policy_authorize(SeventPolicy::class, 'update', $context, $sevent);

        if (isset($attributes['privacy'])) {
            if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
                $sevent->setPrivacyListAttribute($attributes['list']);
            }

            if (!$context->can('updatePrivacy', [$sevent, $attributes['privacy']])) {
                throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
            }
        }

        if (isset($attributes['title'])) {
            $attributes['title'] = $this->cleanTitle($attributes['title']);
        }

        if ($removeImage) {
            $image = $sevent->image_file_id;
            app('storage')->deleteFile($image, null);
            $attributes['image_file_id'] = null;
        }

        if ($attributes['temp_file'] > 0) {
            $attributes['image_file_id'] = upload()->getFileId($attributes['temp_file'], true);
        }

        if ($removeImage) {
            $image = $sevent->host_image_file_id;
            app('storage')->deleteFile($image, null);
            $attributes['host_image_file_id'] = null;
        }

        if ($attributes['host_temp_file'] > 0) {
            $attributes['host_image_file_id'] = upload()->getFileId($attributes['host_temp_file'], true);
        }
        
        $sevent->fill($attributes);

        $sevent->save();

        resolve(AttachmentRepositoryInterface::class)
            ->updateItemId($attributes['attachments'] ?? null, $sevent);

        $this->handleAttachedPhotos($context, $sevent, Arr::get($attributes, 'attached_photos'));

        $sevent->refresh();

        $this->updateFeedStatus($sevent);

        return $sevent;
    }

    protected function updateFeedStatus(Sevent $sevent): void
    {
        
    }

    public function deleteSevent(User $user, $id): int
    {
        $resource = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(SeventPolicy::class, 'delete', $user, $resource);

        return $this->delete($id);
    }

    public function viewSevents(User $context, User $owner, array $attributes): Paginator
    {
        $limit = $attributes['limit'];
        $view = $attributes['view'];
        $profileId = $attributes['user_id'];
        
        $this->withUserMorphTypeActiveScope();

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit);
        }

        if ($profileId > 0 && $profileId == $context->entityId()) {
            $attributes['view'] = $view = Browse::VIEW_MY;
        }

        if (Browse::VIEW_PENDING == $view) {
            if (Arr::get($attributes, 'user_id') == 0) {
                if ($context->isGuest() || !$context->hasPermissionTo('sevent.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }
            }
        }

        $categoryId = Arr::get($attributes, 'category_id', 0);

        if ($categoryId > 0) {
            $category = $this->categoryRepository()->find($categoryId);
            policy_authorize(CategoryPolicy::class, 'viewActive', $context, $category);
        }

        $query = $this->buildQueryViewSevents($context, $owner, $attributes);
        
        $relations = $this->withRelations();
       
        return $query
            ->with($relations)
            ->simplePaginate($limit, ['sevents.*']);
    }

    protected function withRelations(): array
    {
        return ['seventText', 'user', 'owner', 'userEntity', 'activeCategories'];
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
    private function buildQueryViewSevents(User $context, User $owner, array $attributes): Builder
    {
        $sort = !empty($attributes['sortPopular']) ? $attributes['sortPopular'] : 
            ($attributes['sort'] ? $attributes['sort'] : '');
        $sortType = $attributes['sort_type'];
        
        $when = !empty($attributes['whenPopular']) ? $attributes['whenPopular'] : 
            ($attributes['when'] ? $attributes['when'] : '');
            
        $view = $attributes['view'] ?? '';
        $search = $attributes['q'] ?? '';
        $sview = $attributes['sview'] ?? '';
        $distance = $attributes['distance'] ?? '';
        $excludeSeventId = $attributes['exclude_sevent_id'] ?? '';
        $searchTag = $attributes['tag'] ?? '';
        $categoryId = $attributes['category_id'];
        $profileId = $attributes['user_id']; 
        $courseId = $attributes['course_id'] ?? ''; 
        $isFeatured = Arr::get($attributes, 'is_featured');
        $countryIso = $attributes['country_iso'] ?? '';
        $bounds     = [
            'west'  => Arr::get($attributes, 'bounds_west'),
            'east'  => Arr::get($attributes, 'bounds_east'),
            'south' => Arr::get($attributes, 'bounds_south'),
            'north' => Arr::get($attributes, 'bounds_north'),
        ];

        $boundsScope = new BoundsScope();
        $boundsScope->setBounds($bounds);

        // Scopes.
        $privacyScope = new PrivacyScope();
        $privacyScope
            ->setUserId($context->entityId())
            ->setModerationPermissionName('sevent.moderate');
            
        $sortScope = new SortScope($sort, $sortType);
        
        $whenScope = new WhenScope($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view)->setProfileId($profileId);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($searchTag != '') {
            $query = $query->addScope(new TagScope($searchTag));
        }

        if ($profileId > 0 && $profileId != $context->entityId()) {
            $query->where('sevents.is_draft', '!=', 1);
        }

        if ($courseId > 0)
             $query->where('sevents.course_id', '=', $courseId);
        else
            $query->where('sevents.course_id', '=', 0);

        if (!empty($attributes['sortPopular'])) 
            $query->where('sevents.owner_type', '=', 'user');

        // Sview condition
        if ($sview) {
            $formattedDate = Carbon::now()->format('Y-m-d H:i:s');
            switch ($sview) {
                case 'free':
                    $tickets = Ticket::where('amount', '>', 0)
                    ->pluck('sevent_id');

                    $query->whereNotIn('sevents.id', $tickets);
                    break;
                case 'paid':
                    $query->join('sevent_tickets AS f', function (JoinClause $join) {
                        $join->on('f.sevent_id', '=', 'sevents.id')
                             ->where('f.amount', '>', 0);
                    });
                    break;
                case 'upcoming':
                    $query->where(function ($query) use ($formattedDate) {
                        $query->where('sevents.start_date', '>', $formattedDate);
                    });
                    break;
                case 'ongoing':
                    $query->where(function ($query) use ($formattedDate) {
                        $query->where('sevents.end_date', '>', $formattedDate);
                        $query->where('sevents.start_date', '<', $formattedDate);
                    });
                    break;
                case 'past':
                    $formattedDate = Carbon::now()->format('Y-m-d H:i:s');
                    $query->where(function ($query) use ($formattedDate) {
                        $query->where('sevents.end_date', '<', $formattedDate);
                    });
                    break;
            }
        }
        
        // Distance query
        if ($distance) {
            $lat = '36.778259';
            $long = '-119.417931';
	    $customIp = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
            $ip = $customIp[0];
            $curl = curl_init(); // Initialize a cURL session

            $urlCurl = "https://ipinfo.io/" . $ip . "?token=c318bff414c714";
            curl_setopt_array($curl, [
                CURLOPT_URL => $urlCurl , 
                CURLOPT_RETURNTRANSFER => true, 
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10, 
                CURLOPT_HTTPHEADER => [ 
                    'Content-Type: application/json'
                ],
            ]);

            $userIp = json_decode(curl_exec($curl));
           	
            if (!empty($userIp)) {
                $temp = explode(",",$userIp->loc);
                $lat = $temp[0];
                $long = $temp[1];
            }

            $rangevaluefrom = floatval($distance);
            $glat = floatval($lat);
            $glong = floatval($long);

            $query->whereRaw("(
                        (3959 * acos(
                                cos( radians('{$glat}'))
                                * cos( radians( sevents.location_latitude ) )
                                * cos( radians( sevents.location_longitude ) - radians('{$glong}') )
                                + sin( radians('{$glat}') ) * sin( radians( sevents.location_latitude ) )
                            ) < {$rangevaluefrom}
                        )
                    )");
        }

        if (!empty($countryIso))
            $query->where('sevents.country_iso', '=', $countryIso);

        if ($excludeSeventId > 0)
            $query->where('sevents.id', '!=', $excludeSeventId);

        $query->addScope(new FeaturedScope($isFeatured));

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());

            $viewScope->setIsViewOwner(true);

            if (!policy_check(SeventPolicy::class, 'approve', $context, resolve(Sevent::class))) {
                $query->where('sevents.is_approved', '=', 1);
            }
        }

        $hasCategorySearching = $categoryId > 0;
        match ($hasCategorySearching) {
            true  => $this->buildQueryForSearchingCategory($query, $categoryId),
            false => $this->buildQueryForSearching($query, $attributes),
        };
        
        // remove expired sevent
        if ((empty($sview) or $sview !== 'past') and empty($courseId) and 
            (empty($view) or $view == 'all' or $view == 'similar')) {
            $formattedDate = Carbon::now()->format('Y-m-d H:i:s');
            $query->where(function ($query) use ($formattedDate) {
                $query->where('sevents.end_date', '>', $formattedDate);
            });
        }

        $query = $this->applyDisplaySeventSetting($query, $owner, $view);
        if (!$isFeatured) {
            $query->addScope($privacyScope);
        }

        if ($view == 'similar') {
            $attributes['ex'] ? $query->where('sevents.id', '<>', (int)$attributes['ex']) : null;
            $query->inRandomOrder();
        }

        return $query
            ->addScope($sortScope)
            ->addScope($boundsScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    public function massEmail($context, $id, $attributes)
    {
        $subject    = Arr::get($attributes, 'subject');
        $text       = Arr::get($attributes, 'text');

        $recipients = UserModel::select('users.email')
            ->join('sevent_attends', 'users.id', '=', 'sevent_attends.user_id')
            ->where('sevent_attends.sevent_id', '=', $id)
            ->groupBy('users.email')
            ->groupBy('users.id')
            ->groupBy('sevent_attends.user_id')
            ->groupBy('sevent_attends.sevent_id')
            ->pluck('user.email')
            ->toArray();
            
        Mail::to($recipients)
            ->send(new SeventMail([
                'subject' => $subject,
                'html'    => $text,
            ]));
    }

    protected function buildQueryForSearchingCategory(Builder $query, mixed $categoryId): void
    {
        if (!is_array($categoryId)) {
            $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
        }

        $categoryScope = new CategoryScope();

        $categoryScope->setCategories($categoryId);
        $query->addScope($categoryScope);
    }

    /**
     * @param Builder              $query
     * @param array<string, mixed> $attributes
     * @return void
     */
    protected function buildQueryForSearching(Builder $query, array $attributes): void
    {
        if (Arr::get($attributes, 'view') == Browse::VIEW_SEARCH) {
            $query->leftJoin('sevent_category_data', function (JoinClause $joinClause) {
                $joinClause->on('sevent_category_data.item_id', '=', 'sevents.id');
            })
                ->leftJoin('sevent_categories', function (JoinClause $joinClause) {
                    $joinClause->on('sevent_categories.id', '=', 'sevent_category_data.category_id')
                        ->where('sevent_categories.is_active', 1);
                });
        }
    }

    /**
     * @param Builder $query
     * @param User    $owner
     * @param string  $view
     * @return Builder
     */
    private function applyDisplaySeventSetting(Builder $query, User $owner, string $view): Builder
    {
        if (in_array($view, [Browse::VIEW_MY, ViewScope::VIEW_DRAFT])) {
            return $query;
        }

        if (!$owner instanceof HasPrivacyMember) {
            $query->where('sevents.owner_type', '=', $owner->entityType());
        }

        return $query;
    }

    public function viewSevent(User $context, int $id): Sevent
    {
        $sevent = $this
            ->withUserMorphTypeActiveScope()
//            ->with(['user', 'userEntity', 'categories', 'activeCategories', 'attachments'])
            ->find($id);

        policy_authorize(SeventPolicy::class, 'view', $context, $sevent);

        if ($sevent->isDraft() || $context->isGuest()) {
            return $sevent->refresh();
        }

        $sevent->incrementTotalView();
        $sevent->refresh();

        return $sevent;
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_featured', Sevent::IS_FEATURED)
            ->where('is_approved', Sevent::IS_APPROVED)
            ->where('is_draft', '<>', 1)
            ->where('course_id', '=', 0)
            ->inRandomOrder()
            ->simplePaginate($limit);
    }

    public function getYouMayLikeSevents(int $id, int $limit): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_approved', Sevent::IS_APPROVED)
            ->where('is_draft', '<>', $id)
            ->where('course_id', '=', 0)
            ->inRandomOrder()
            ->simplePaginate($limit);
    }
    
    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('is_sponsor', Sevent::IS_SPONSOR)
            ->where('is_approved', Sevent::IS_APPROVED)
            ->where('is_draft', '<>', 1)
            ->where('course_id', '=', 0)
            ->inRandomOrder()
            ->simplePaginate($limit);
    }

    public function publish(User $user, int $id): Sevent
    {
        $sevent = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(SeventPolicy::class, 'publish', $user, $sevent);

        if (!$sevent->isPublished()) {
            $sevent->is_draft = 0;

            if (!$user->hasPermissionTo('sevent.auto_approved')) {
                $sevent->is_approved = 0;
            }

            $sevent->save();

            app('sevents')->dispatch('notification.new_post_to_follower', [$user, $sevent]);
        }

        return $sevent->refresh();
    }

    public function getTotalSeventsCount(): int
    {
        return $this->getModel()->newModelQuery()->count();
    }
}