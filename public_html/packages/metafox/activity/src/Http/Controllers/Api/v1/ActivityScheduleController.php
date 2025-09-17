<?php

namespace MetaFox\Activity\Http\Controllers\Api\v1;

use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use MetaFox\Activity\Http\Requests\v1\ActivitySchedule\IndexRequest;
use MetaFox\Activity\Http\Requests\v1\ActivitySchedule\StoreRequest;
use MetaFox\Activity\Http\Requests\v1\ActivitySchedule\UpdateRequest;
use MetaFox\Activity\Http\Resources\v1\ActivitySchedule\ActivityScheduleDetail as Detail;
use MetaFox\Activity\Http\Resources\v1\ActivitySchedule\ActivityScheduleForEdit;
use MetaFox\Activity\Http\Resources\v1\ActivitySchedule\ActivityScheduleItemCollection as ItemCollection;
use MetaFox\Activity\Models\ActivitySchedule;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\ActivityScheduleRepositoryInterface;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Traits\Eloquent\Model\HasFilterTagUserTrait;
use MetaFox\User\Support\Facades\UserPrivacy;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Activity\Http\Controllers\Api\ActivityScheduleController::$controllers;.
 */

/**
 * Class ActivityScheduleController.
 * @codeCoverageIgnore
 * @ignore
 */
class ActivityScheduleController extends ApiController
{
    use HasFilterTagUserTrait;

    /**
     * @var ActivityScheduleRepositoryInterface
     */
    private ActivityScheduleRepositoryInterface $repository;

    /**
     * ActivityScheduleController Constructor.
     *
     * @param ActivityScheduleRepositoryInterface $repository
     */
    public function __construct(ActivityScheduleRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse item.
     *
     * @param IndexRequest $request
     * @return mixed
     * @throws AuthorizationException|AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();
        $owner   = $context;

        $data = $this->repository->viewScheduledPosts($context, $owner, $params);

        return new ItemCollection($data);
    }

    /**
     * Store item.
     *
     * @param StoreRequest $request
     *
     * @return Detail
     * @throws ValidatorException
     */
    public function store(StoreRequest $request): Detail
    {
        $params  = $request->validated();

        $context = user();

        $quotaCheckData = [
            'where'        => [
                'from_resource' => Feed::FROM_FEED_RESOURCE,
            ],
            'second_extra' => [
                'entity_type' => ActivitySchedule::ENTITY_TYPE,
                'column'      => 'schedule_time',
            ],
        ];

        app('quota')->checkQuotaControlWhenCreateItem($context, Feed::ENTITY_TYPE, 1, $quotaCheckData);

        $data = $this->repository->create($params);

        return new Detail($data);
    }

    /**
     * View item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function show($id): JsonResponse
    {
        $data = $this->repository->viewScheduledPost(user(), $id);

        return $this->success(new Detail($data));
    }

    /**
     * Update item.
     *
     * @param UpdateRequest $request
     * @param int           $id
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws BindingResolutionException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params       = $request->validated();
        $context      = user();
        $user         = $params['user'];
        $owner        = $params['owner'];
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

        app('flood')->checkFloodControlWhenCreateItem($context, Feed::ENTITY_TYPE);
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
                'isEdit'   => true,
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
        $data = $this->repository->updateScheduledPost($context, $user, $id, $params);

        $now     = Carbon::make(MetaFox::clientDate());
        $message = __p('activity::phrase.your_post_will_be_sent_on_time', ['time' => Carbon::make($scheduleTime)->setTimezone($now->timezone ?? '')->format('m/d/Y H:i')]);

        return $this->success(new Detail($data), [], $message);
    }

    public function edit(int $id): JsonResponse
    {
        $resource = $this->repository->getForEdit(user(), $id);

        return $this->success(new ActivityScheduleForEdit($resource));
    }

    public function sendNow(int $id): JsonResponse
    {
        $response = $this->repository->sendNowScheduledPost(user(), $id);
        if (!$response) {
            abort(400, __('validation.something_went_wrong_please_try_again'));
        }

        return $this->success([
            'id' => $id,
        ], [], __p('activity::phrase.feed_created_successfully'));
    }

    /**
     * Delete item.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function destroy(int $id): JsonResponse
    {
        $response = $this->repository->deleteScheduledPost(user(), $id);
        if (!$response) {
            abort(400, __('validation.something_went_wrong_please_try_again'));
        }

        return $this->success([
            'id' => $id,
        ], [], __p('activity::phrase.scheduled_post_was_deleted_successfully'));
    }
}
