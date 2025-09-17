<?php

namespace MetaFox\Friend\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use MetaFox\Friend\Http\Requests\v1\Friend\FriendBirthdaysRequest;
use MetaFox\Friend\Http\Requests\v1\Friend\HideUserSuggestRequest;
use MetaFox\Friend\Http\Requests\v1\Friend\IndexRequest;
use MetaFox\Friend\Http\Requests\v1\Friend\InviteFriendsToItemRequest;
use MetaFox\Friend\Http\Requests\v1\Friend\InviteFriendToOwnerRequest;
use MetaFox\Friend\Http\Requests\v1\Friend\MentionRequest;
use MetaFox\Friend\Http\Requests\v1\Friend\SuggestRequest;
use MetaFox\Friend\Http\Requests\v1\Friend\TagSuggestionRequest;
use MetaFox\Friend\Http\Resources\v1\Friend\FriendItemCollection;
use MetaFox\Friend\Http\Resources\v1\Friend\FriendMentionCollection;
use MetaFox\Friend\Http\Resources\v1\Friend\FriendSimpleCollection;
use MetaFox\Friend\Http\Resources\v1\Friend\SuggestionItemCollection;
use MetaFox\Friend\Http\Resources\v1\Friend\SuggestionSimpleCollection;
use MetaFox\Friend\Policies\FriendPolicy;
use MetaFox\Friend\Repositories\FriendRepositoryInterface;
use MetaFox\Friend\Repositories\TagFriendRepositoryInterface;
use MetaFox\Friend\Support\Friend;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFox;
use MetaFox\User\Http\Resources\v1\User\BirthdayItemCollection;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;

/**
 * Class FriendController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group friend
 */
class FriendController extends ApiController
{
    public function __construct(
        protected FriendRepositoryInterface    $repository,
        protected TagFriendRepositoryInterface $tagFriendRepository
    ) {}

    /**
     * Browse friends.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function index(IndexRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $owner = $context;

        $userId = Arr::get($params, 'user_id');
        if ($userId) {
            $owner = UserEntity::getById((int) $userId)->detail;

            policy_authorize(FriendPolicy::class, 'viewOnProfilePage', $context, $owner);

            if (!UserPrivacy::hasAccess($context, $owner, 'friend.profile_menu')) {
                return $this->success([]);
            }
        }

        // Does not throw exception
        if (!policy_check(FriendPolicy::class, 'viewAny', $context, $owner)) {
            return $this->success([]);
        }

        $data = $this->repository->viewFriends($context, $owner, $params);

        return $this->success(new FriendItemCollection($data));
    }

    /**
     * Load mentions.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     *
     * @throws AuthorizationException|AuthenticationException
     */
    public function mention(MentionRequest $request)
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->getMentions($context, $params);

        if (null == $data) {
            return $this->success([]);
        }

        return $this->success(new FriendMentionCollection($data));
    }

    /**
     * Remove friend.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->repository->unFriend((int) Auth::id(), $id);

        if ($result == false) {
            return $this->error(__p('friend::phrase.cant_un_friend'));
        }

        return $this->success(['id' => $id], [], __p('friend::phrase.un_friend_successfully'));
    }

    /**
     * Load suggestion.
     *
     * @param SuggestRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function suggestion(SuggestRequest $request)
    {
        $params = $request->validated();

        $view = Arr::get($params, 'view');

        $context = user();

        if ($context->isGuest()) {
            return new SuggestionSimpleCollection([]);
        }

        if (Friend::VIEW_AVAILABLE_SUGGESTION === $view) {
            return new SuggestionSimpleCollection($this->repository->getAvailableAddingFriendSuggestion($context, $params));
        }

        $data = $this->repository->getFriendSuggestion($context, $params);

        if (MetaFox::isMobile() && version_compare(MetaFox::getApiVersion(), 'v1.8', '<')) {
            return new FriendItemCollection($data);
        }

        return new SuggestionSimpleCollection($data);
    }

    /**
     * Load friend suggestion for tagging.
     *
     * @param TagSuggestionRequest $request
     * @bodyParam excluded_ids int[]
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function tagSuggestion(TagSuggestionRequest $request)
    {
        $params = $request->validated();

        $data = $this->repository->getTagSuggestions(user(), $params);

        return $this->success(new FriendItemCollection($data));
    }

    /**
     * Hide user suggestions.
     *
     * @param HideUserSuggestRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function hideUserSuggestion(HideUserSuggestRequest $request): JsonResponse
    {
        $params = $request->validated();

        $user = UserEntity::getById($params['user_id'])->detail;
        $this->repository->hideUserSuggestion(user(), $user);

        return $this->success();
    }

    /**
     * Get birthday.
     *
     * @param FriendBirthdaysRequest $request
     *
     * @throws AuthenticationException
     */
    public function getFriendBirthdays(FriendBirthdaysRequest $request)
    {
        $params = $request->validated();
        $data   = $this->repository->getFriendBirthdays(user(), $params);

        return new BirthdayItemCollection($data);
    }

    /**
     * Invite friend.
     *
     * @param InviteFriendToOwnerRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function inviteFriendToOwner(InviteFriendToOwnerRequest $request)
    {
        $context = user();
        $params  = $request->validated();

        $data = $this->repository->inviteFriendToOwner($context, $params);

        return new FriendSimpleCollection($data);
    }

    /**
     * @param InviteFriendsToItemRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function inviteFriendsToItem(InviteFriendsToItemRequest $request): JsonResponse
    {
        $context = user();

        $params = $request->validated();

        $data = $this->repository->inviteFriendsToItem($context, $params);

        return $this->success(new FriendSimpleCollection($data));
    }

    /**
     * @param MentionRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function shareSuggestion(MentionRequest $request): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $data = $this->repository->getMentions($context, $params);

        if (null == $data) {
            return $this->success([]);
        }

        return $this->success(new SuggestionItemCollection($data));
    }
}
