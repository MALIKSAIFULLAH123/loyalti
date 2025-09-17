<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Activity\Http\Requests\v1\Feed\AllowPreviewTagRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\CheckNewRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\DeclinePendingRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\IndexRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\ShareRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\ShowRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\StoreRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\TaggedFriendsRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\TranslateRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\UpdatePrivacyRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\UpdateRequest;
use MetaFox\Activity\Http\Requests\v1\Feed\UpdateSortRequest;
use MetaFox\Activity\Http\Resources\v1\Feed\FeedDetail as Detail;
use MetaFox\Activity\Http\Resources\v1\Feed\FeedForEdit;
use MetaFox\Activity\Http\Resources\v1\Feed\FeedItemCollection;
use MetaFox\Activity\Http\Resources\v1\Feed\FeedTranslateItem;
use MetaFox\Activity\Http\Resources\v1\Share\FeedShareForm;
use MetaFox\Activity\Models\ActivitySchedule;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Policies\FeedPolicy;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Repositories\ShareRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\Link;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;
use MetaFox\User\Support\Facades\User as UserFacades;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Support\User as UserSupport;

/**
 * --------------------------------------------------------------------------
 *  Api Controller
 * --------------------------------------------------------------------------.
 * Assign this class in $controllers of.
 *
 * @link \MetaFox\Activity\Http\Controllers\Api\FeedController::$controllers;
 */

/**
 * Class FeedController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 *
 * @group feed
 * @authenticated
 * @ignore
 * @codeCoverageIgnore
 */
class FeedController extends ApiController
{
    use HasFilterTagUserTrait;

    private FeedRepositoryInterface $feedRepository;

    /**
     * @param FeedRepositoryInterface $feedRepository
     */
    public function __construct(FeedRepositoryInterface $feedRepository)
    {
        $this->feedRepository = $feedRepository;
    }

    /**
     * Browse feed item.
     *
     * @param IndexRequest $request
     * @bodyParam type_id string Example: blog
     *
     * @return array<string,mixed>|JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function index(IndexRequest $request)
    {
        $context         = user();
        $params          = $request->validated();
        $extraConditions = [];
        $view            = Arr::get($params, 'view');
        $isPreviewTag    = Arr::get($params, 'is_preview_tag', false);
        $sort            = $params['sort'];
        $sortType        = $params['sort_type'];
        $owner           = null;
        $ownerId         = Arr::get($params, 'user_id', 0);
        $isMemberView    = false;
        $status          = Arr::get($params, 'status', MetaFoxConstant::ITEM_STATUS_APPROVED);

        $this->feedRepository->updateUserValueSortFeed($context, $params);

        if ($ownerId > 0) {
            $owner = UserEntity::getById($ownerId)->detail;

            // If viewed on profile, but you don't have permission to view their profile, should see empty feed listing.
            if (!policy_check(FeedPolicy::class, 'viewOnProfilePage', $context, $owner)) {
                return $this->success();
            }

            if ($owner->hasPendingMode()) {
                $isYour = $view == Browse::VIEW_YOUR_CONTENT;

                if (!policy_check(FeedPolicy::class, 'viewContent', $context, $owner, $status, $isYour)) {
                    if ($isYour) {
                        return $this->error(__p('core::phrase.content_is_not_available'), 403);
                    }

                    return $this->success();
                }

                $isMemberView = $isYour;
            }
        }

        if (!policy_check(FeedPolicy::class, 'viewAny', $context, $owner)) {
            return $this->success();
        }

        if (null !== $params['from']) {
            $extraConditions['where'][] = ['stream.owner_type', '=', $params['from'], 'and'];
        }

        if (null !== $params['type_id']) {
            switch ($params['type_id']) {
                case IndexRequest::VIEW_ACTIVITY_POST:
                    $extraConditions['where'][] = [
                        ['stream.item_type', '=', Link::ENTITY_TYPE, 'or'],
                        ['stream.item_type', '=', $params['type_id'], 'or'],
                    ];
                    break;
                case IndexRequest::VIEW_MEDIA:
                    $extraConditions['where'][] = [
                        ['stream.item_type', '=', 'photo', 'or'],
                        ['stream.item_type', '=', 'photo_set', 'or'],
                    ];
                    break;
                default:
                    $extraConditions['where'][] = ['stream.item_type', '=', $params['type_id'], 'and'];
                    break;
            }
        }

        if ($isMemberView) {
            $extraConditions['where'][] = [
                'feed.user_id', '=', $context->entityId(), 'and',
            ];
        }

        /**
         * Default getting value by setting.
         */
        $onlyFriends = (bool) Settings::get('activity.feed.only_friends', true);

        /*
         * In case searching, force query by searching value
         */
        if (Arr::has($params, 'related_comment_friend_only')) {
            $onlyFriends = (bool) $params['related_comment_friend_only'];
        }

        $owner = $isPreviewTag ? $context : $owner;

        $feeds = $this->feedRepository->getFeeds(
            $context,
            $owner,
            $params['last_feed_id'],
            $params['limit'],
            $params['hashtag'],
            $onlyFriends,
            $extraConditions,
            $sort,
            $sortType,
            $status == MetaFoxConstant::ITEM_STATUS_APPROVED,
            [$status],
            $isPreviewTag,
            Arr::get($params, 'sponsored_feed_ids'),
            $params
        );

        if (in_array($status, [MetaFoxConstant::ITEM_STATUS_PENDING, MetaFoxConstant::ITEM_STATUS_DENIED])) {
            request()->request->add([
                'is_view_pending_feed' => true,
            ]);
        }

        $lastFeed = $feeds->last();

        $lastFeedId = $lastFeed instanceof Feed ? $lastFeed->entityId() : 0;

        $response = [];

        try {
            $collection = new FeedItemCollection($feeds);
            LoadReduce::capture($collection);
            $response = [
                'data'       => $collection,
                'pagination' => [
                    'last_feed_id'       => $lastFeedId,
                    'sponsored_feed_ids' => request()->get('pagination_sponsored_feed_ids'),
                ],
                'meta'       => [
                    'sort_feed_preferences' => UserFacades::getReferenceValueByName($context, UserSupport::SORT_FEED_VALUES_SETTING) ?? [],
                ],
            ];
        } catch (Exception $e) {
            Log::info(print_r($feeds, true));
            abort(500, $e->getMessage());
        }

        return response()->json($response);
    }

    /**
     * Create feed.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params       = $request->validated();
        $context      = user();
        $user         = $params['user'];
        $owner        = $params['owner'];
        $data         = [];
        $postType     = Arr::get($params, 'post_type');
        $scheduleTime = Arr::get($params, 'schedule_time');

        unset($params['user'], $params['owner']);
        $quotaCheckData = [
            'where'        => [
                'from_resource' => Feed::FROM_FEED_RESOURCE,
            ],
            'created_at'   => $scheduleTime,
            'second_extra' => [
                'entity_type' => ActivitySchedule::ENTITY_TYPE,
                'column'      => 'schedule_time',
            ],
        ];
        $floodCheckData = [
            'where' => [
                'from_resource' => Feed::FROM_FEED_RESOURCE,
            ],
        ];

        app('flood')->checkFloodControlWhenCreateItem($context, Feed::ENTITY_TYPE, $floodCheckData);
        app('quota')->checkQuotaControlWhenCreateItem($context, Feed::ENTITY_TYPE, 1, $quotaCheckData);

        if (!UserPrivacy::hasAccess($user, $owner, 'feed.share_on_wall')) {
            abort(403, __p('activity::phrase.unable_to_share_this_post_due_to_privacy_setting'));
        }

        $taggedFriends = Arr::get($params, 'tagged_friends');

        if (is_array($taggedFriends) && count($params['tagged_friends'])) {
            $params = array_merge($params, $this->transformTaggedFriends(
                $context,
                $user,
                $owner,
                $taggedFriends,
                Arr::get($params, 'content')
            ));

            $driver = resolve(DriverRepositoryInterface::class)
                ->getDriver(Constants::DRIVER_TYPE_FORM, $postType . '.feed_form', 'web');

            $form = app()->make($driver, [
                'resource' => null,
                'isEdit'   => false,
            ]);

            /*
             * Validate tagged friends again after filter invalid mentions/tags
             */
            if (method_exists($form, 'validate')) {
                app()->call([$form, 'validate'], [
                    'data' => $params,
                ]);
            }
        }

        if ($scheduleTime) {
            return $this->storeSchedule($context, $user, $owner, $params, $data, $scheduleTime);
        }

        $resource = $this->feedRepository->createFeed($context, $user, $owner, $params);

        /*
         * This call may generate the following variables base on each specific cases:
         * $feed : The resource Feed itself
         * $message: for some custom message
         */
        extract($resource);

        $message = $message ?? __p('activity::phrase.feed_created_successfully');

        if (!isset($feed)) {
            return $this->info([], [], $message);
        }

        if ($feed->streamPending() || !$feed->is_approved) {
            if ($feed->owner instanceof HasPrivacyMember) {
                $message = $feed->getOwnerPendingMessage();
            }

            return $this->success(['id' => 0], [], $message);
        }

        if (policy_check(FeedPolicy::class, 'view', $context, $feed)) {
            $data = new Detail($feed);
        }

        return $this->success($data, [], $message);
    }

    /**
     * @param ShowRequest $request
     * @param int         $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws \Laravel\Octane\Exceptions\DdException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function show(ShowRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $user   = user();

        $resource = $this->feedRepository->getFeed($user, $id);

        $this->addPendingFeedFlag($user, $resource);
        $this->addProfileFeedFlag($params);

        return $this->success(new Detail($resource));
    }

    protected function addPendingFeedFlag(User $user, Feed $resource): void
    {
        if ($resource->isApproved()) {
            return;
        }

        if ($user->entityId() !== $resource->userId()) {
            return;
        }

        request()->request->add([
            'is_view_pending_feed' => true,
        ]);
    }

    protected function addProfileFeedFlag(array $params): void
    {
        $ownerId = Arr::get($params, 'user_id');

        if (!$ownerId) {
            return;
        }

        $owner = UserEntity::getById($ownerId)->detail;

        if (!$owner instanceof User) {
            return;
        }

        request()->merge(['is_profile_feed' => true]);
    }

    /**
     * Update feed item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $feed = $this->feedRepository->find($id);

        $user = $params['user'];

        if (Arr::has($params, 'tagged_friends')) {
            $taggedFriends = Arr::get($params, 'tagged_friends') ?: [];

            if (count($taggedFriends)) {
                $params = array_merge($params, $this->transformTaggedFriends(
                    $context,
                    $user,
                    $feed->owner,
                    $taggedFriends,
                    Arr::get($params, 'content'),
                    $feed->item,
                ));
            }
        }

        $resource = $this->feedRepository->updateFeed($context, $user, $id, $params);

        $hasChangeStatus = !$feed->is_approved && $feed->status != $resource->status;

        switch ($hasChangeStatus) {
            case true:
                //Remove item when status is changing
                $response = [
                    'nextAction' => [
                        'type'    => 'feed/delete',
                        'payload' => [
                            'id' => $feed->entityId(),
                        ],
                    ],
                ];
                $message  = __p('activity::phrase.thanks_for_editting_your_post_for_approval');
                break;
            default:
                request()->request->add([
                    'is_view_pending_feed' => true,
                ]);

                $response = new Detail($resource);
                $message  = __p('activity::phrase.feed_edit_successfully');
                break;
        }

        return $this->success($response, [], $message);
    }

    /**
     * Update feed privacy.
     *
     * @param UpdatePrivacyRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function updatePrivacy(UpdatePrivacyRequest $request, int $id): JsonResponse
    {
        $context = user();
        $feed    = $this->feedRepository->find($id);
        $params  = $request->validated();

        policy_authorize(FeedPolicy::class, 'changePrivacyFromFeed', $context, $feed);

        $resource = $this->feedRepository->updateFeedPrivacy($context, $feed, $params);

        return $this->success(new Detail($resource), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Delete feed item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->feedRepository->deleteFeed(user(), $id);
        if (!$response) {
            abort(400, __('validation.something_went_wrong_please_try_again'));
        }

        return $this->success(['id' => $id], [], __p('activity::phrase.feed_deleted_successfully'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function deleteWithItems(int $id): JsonResponse
    {
        $response = $this->feedRepository->deleteFeedWithItems(user(), $id);

        if (!$response) {
            abort(400, __('validation.something_went_wrong_please_try_again'));
        }

        return $this->success(['id' => $id], [], __p('activity::phrase.feed_deleted_post_with_items_successfully'));
    }

    /**
     * Share feed item.
     *
     * @param ShareRequest $request
     * @bodyParam type_id string
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function share(ShareRequest $request): JsonResponse
    {
        $params = $request->validated();

        $user = user();

        $targets = Arr::get($params, 'owners', []);

        $owners = $feedIds = [];

        if (is_array($targets)) {
            foreach ($targets as $ownerId) {
                $owner = UserEntity::getById($ownerId)->detail;

                if (!UserPrivacy::hasAccess($user, $owner, 'feed.share_on_wall')) {
                    continue;
                }

                $owners[] = $owner;
            }
        }

        if (!count($owners)) {
            abort(403, __p('activity::phrase.unable_to_share_this_post_due_to_privacy_setting'));
        }

        $shareRepository = resolve(ShareRepositoryInterface::class);

        $taggedFriends = Arr::get($params, 'tagged_friends');

        $hasTaggedFriends = is_array($taggedFriends) && count($taggedFriends);

        foreach ($owners as $owner) {
            $ownerParams = $params;

            if ($hasTaggedFriends) {
                $ownerParams = array_merge($ownerParams, $this->transformTaggedFriends(
                    $user,
                    $user,
                    $owner,
                    $taggedFriends,
                    Arr::get($params, 'content')
                ));
            }

            $id = $shareRepository->share($user, $owner, $ownerParams);

            if ($id) {
                $feedIds[] = $id;
            }
        }

        $message = Arr::get($params, 'success_message', __p('activity::phrase.shared_successfully'));

        return $this->success(['ids' => $feedIds], [], $message);
    }

    /**
     * View share form.
     * GET: feed/share/form.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function shareForm(): JsonResponse
    {
        return $this->success(new FeedShareForm(user()));
    }

    /**
     * Get status for edit.
     * GET: feed/edit/{id}.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function getStatusForEdit(int $id): JsonResponse
    {
        $resource = $this->feedRepository->getFeedForEdit(user(), $id);

        return $this->success(new FeedForEdit($resource));
    }

    /**
     * Get post types.
     *
     * @return JsonResponse
     */
    public function postType(): JsonResponse
    {
        // @todo what is it ?
        return $this->success([]);
    }

    /**
     * Remove tag.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeTag(int $id): JsonResponse
    {
        $feed = $this->feedRepository->find($id);
        $user = user();

        $this->feedRepository->removeTagFriend($feed);

        $resource = $this->feedRepository->getFeed($user, $id);

        return $this->success(
            new Detail($resource),
            [],
            __p('activity::phrase.removed_tag_successfully')
        );
    }

    /**
     * Get tagged friends.
     * GET: /feed/tagged-friend.
     *
     * @param TaggedFriendsRequest $request
     *
     * @return JsonResponse|JsonResource
     * @throws AuthorizationException|AuthenticationException
     */
    public function getTaggedFriends(TaggedFriendsRequest $request)
    {
        if (!app_active('metafox/friend')) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        $params = $request->validated();

        if ($params['item_type'] == ActivitySchedule::ENTITY_TYPE) {
            /** @var ActivityScheduleRepositoryInterface $scheduleRepository */
            $scheduleRepository = resolve(ActivityScheduleRepositoryInterface::class);
            $schedule           = $scheduleRepository->viewScheduledPost(user(), $params['item_id']);
            $data               = $scheduleRepository->getScheduledTaggedFriend($schedule, $params['limit']);

            return new UserEntityCollection($data);
        }

        $data = $this->feedRepository->getTaggedFriends($params['item_id'], $params['item_type'], $params['limit'], Arr::get($params, 'excluded_ids', []));

        return new UserEntityCollection($data);
    }

    /**
     * GET: feed/manage-hidden.
     *
     * @return void
     */
    public function getManageHiddens()
    {
        abort(400, __('validation.not_supported'));
    }

    /**
     * GET: feed/manage-hidden/{id}.
     *
     * @param int $id
     *
     * @return void
     */
    public function getManageHiddenDetail(int $id)
    {
        abort(400, __('validation.not_supported'));
    }

    /**
     * DELETE: feed/manage-hidden/{id}.
     *
     * @param int $id
     *
     * @return void
     */
    public function deleteHidden(int $id)
    {
        abort(400, __('validation.not_supported'));
    }

    /**
     * Approve pending post.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approvePendingFeed(int $id): JsonResponse
    {
        $result = $this->feedRepository->approvePendingFeed(user(), $id);

        return $this->success(...$result);
    }

    /**
     * @param DeclinePendingRequest $request
     * @param int                   $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function declinePendingFeed(DeclinePendingRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $feed = $this->feedRepository->find($id);

        $result = $this->feedRepository->declinePendingFeed(user(), $id, $params);

        $message = __p('activity::phrase.user_post_declined', ['user' => $feed->user?->full_name ?? '']);
        if ($params['is_block_author'] == 1) {
            $message = __p('activity::phrase.this_user_has_been_blocked_and_the_post_has_been_declined');
        }
        if (!$result) {
            return $this->error(__p('validation.something_went_wrong_please_try_again'));
        }

        return $this->success(['id' => $id], [], $message);
    }

    public function allowReviewTag(AllowPreviewTagRequest $request, int $id): JsonResponse
    {
        $context = user();
        $params  = $request->validated();
        $feed    = $this->feedRepository->find($id);
        $result  = $this->feedRepository->allowReviewTag($context, $feed, $params);
        if (!$result) {
            return $this->error();
        }

        return $this->success([], [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function archive(int $id): JsonResponse
    {
        $context = user();

        $success = $this->feedRepository->archiveFeed($context, $id);

        if (!$success) {
            return $this->error(__('validation.no_permission'));
        }

        return $this->success(['id' => $id], [], __p('activity::phrase.post_successfully_removed'));
    }

    public function checkNew(CheckNewRequest $request): JsonResponse
    {
        $context = user();

        /**
         * TODO: implement when new posts feature implemented for page/group.
         */
        $owner = null;

        $data = $request->validated();

        $reload = $this->feedRepository->hasNewFeeds(
            $context,
            Arr::get($data, 'last_feed_id'),
            Arr::get($data, 'last_pin_feed_id'),
            $owner,
            Arr::get($data, 'sort'),
            Arr::get($data, 'last_sponsored_feed_id', 0),
        );

        return $this->success([
            'reload' => $reload,
        ]);
    }

    /**
     * hot fix because of /api/v1/feed/create crashed.
     *
     * @return JsonResponse
     */
    public function create()
    {
        return $this->success([]);
    }

    public function translate(TranslateRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $feed = $this->feedRepository->find(Arr::get($params, 'id'));

        $translated = $this->feedRepository->translateFeed($feed, $context, $params);

        if (!$translated) {
            return $this->error();
        }

        $translatedFeed                     = $feed->replicate();
        $translatedFeed->translated_content = $translated['translated_text'];
        $translatedFeed->target             = $translated['target'];

        return $this->success(new FeedTranslateItem($translatedFeed));
    }

    /**
     * @param User  $context
     * @param mixed $user
     * @param mixed $owner
     * @param array $params
     * @param array $data
     * @param mixed $scheduleTime
     *
     * @return JsonResponse
     */
    private function storeSchedule(User $context, mixed $user, mixed $owner, array $params, array $data, mixed $scheduleTime): JsonResponse
    {
        $now = Carbon::make(MetaFox::clientDate());
        if ($this->feedRepository->createSchedulePost($context, $user, $owner, $params)) {
            return $this->success($data, [], __p('activity::phrase.your_post_will_be_sent_on_time', ['time' => Carbon::make($scheduleTime)->setTimezone($now->timezone ?? '')->format('m/d/Y H:i')]));
        }

        return $this->error();
    }

    /**
     * Update the feed sort value for a user.
     *
     * @param UpdateSortRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function updateSortFeed(UpdateSortRequest $request): JsonResponse
    {
        $context = user();
        $params  = $request->validated();

        $this->feedRepository->updateUserValueSortFeed($context, $params);

        return $this->success([]);
    }
}
