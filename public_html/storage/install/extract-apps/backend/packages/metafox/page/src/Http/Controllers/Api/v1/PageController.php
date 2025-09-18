<?php

namespace MetaFox\Page\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use MetaFox\Page\Http\Requests\v1\Page\IndexRequest;
use MetaFox\Page\Http\Requests\v1\Page\MentionRequest;
use MetaFox\Page\Http\Requests\v1\Page\SimilarRequest;
use MetaFox\Page\Http\Requests\v1\Page\StoreRequest;
use MetaFox\Page\Http\Requests\v1\Page\UpdateAvatarRequest;
use MetaFox\Page\Http\Requests\v1\Page\UpdateCoverRequest;
use MetaFox\Page\Http\Requests\v1\Page\UpdateProfileRequest;
use MetaFox\Page\Http\Requests\v1\Page\UpdateRequest;
use MetaFox\Page\Http\Resources\v1\Page\PageDetail;
use MetaFox\Page\Http\Resources\v1\Page\PageDetail as Detail;
use MetaFox\Page\Http\Resources\v1\Page\PageInfo as InfoDetail;
use MetaFox\Page\Http\Resources\v1\Page\PageItemCollection as ItemCollection;
use MetaFox\Page\Http\Resources\v1\Page\PageSimpleCollection;
use MetaFox\Page\Http\Resources\v1\Page\PageSuggestionCollection;
use MetaFox\Page\Models\Page;
use MetaFox\Page\Policies\PagePolicy;
use MetaFox\Page\Repositories\InfoSettingRepositoryInterface;
use MetaFox\Page\Repositories\PageClaimRepositoryInterface;
use MetaFox\Page\Repositories\PageRepositoryInterface;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class PageController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageController extends ApiController
{
    public function __construct(
        protected PageRepositoryInterface $repository,
        protected UserPrivacyRepositoryInterface $privacyRepository,
        protected PageClaimRepositoryInterface $claimRepository,
        protected InfoSettingRepositoryInterface $infoSettingRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
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
        $limit   = Arr::get($params, 'limit');

        if ($params['user_id'] > 0) {
            $owner = UserEntity::getById($params['user_id'])->detail;
            if (!policy_check(PagePolicy::class, 'viewOnProfilePage', $context, $owner)) {
                throw new AuthorizationException();
            }
        }

        policy_authorize(PagePolicy::class, 'viewAny', $context);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewPages($context, $owner, $params),
        };

        return $this->success(new ItemCollection($data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     * @throws PermissionDeniedException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        app('flood')->checkFloodControlWhenCreateItem($context, Page::ENTITY_TYPE);

        $page = $this->repository->createPage(user(), $params);

        $message = __p('core::phrase.resource_create_success', [
            'resource_name' => __p('page::phrase.page'),
        ]);

        if (!$page->isApproved()) {
            $message = __p('core::phrase.thanks_for_your_item_for_approval');
        }
        $meta = $this->repository->askingForPurchasingSponsorship($context, $page);

        return $this->success(new Detail($page), $meta, $message);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return Detail
     * @throws AuthenticationException|AuthorizationException
     */
    public function show(int $id): Detail
    {
        $data = $this->repository->viewPage(user(), $id);

        return new Detail($data);
    }

    /**
     * Update the specified resource in storage.
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
        $page   = $this->repository->updatePage(user(), $id, $params);

        $key     = array_key_first($params);
        $message = __p("page::phrase.page_updated.$key");

        unset($params['location_latitude'], $params['location_longitude']);
        if (count($params) > 1) {
            $message = __p('page::phrase.page_updated.info');
        }

        return $this->success(new Detail($page), [], $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $page         = $this->repository->find($id);
        $policyMethod = $page->isApproved() ? 'delete' : 'decline';

        policy_authorize(PagePolicy::class, $policyMethod, user(), $page);

        $this->repository->deletePage(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('page::phrase.successfully_deleted_the_page'));
    }

    /**
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

        $page = $this->repository->find($id);

        $isSponsor        = (bool) $sponsor;
        $isPendingSponsor = $isSponsor && !$page->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');
        $message = __p($message, ['resource_name' => __p('page::phrase.page')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($page), [], $message);
    }

    /**
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
        $feature = (int) $params['feature'];
        $context = user();

        match ($feature) {
            1       => $this->repository->featureFree($context, $id),
            default => $this->repository->unfeature($context, $id),
        };

        $message = match ($feature) {
            1       => __p('page::phrase.page_featured_successfully'),
            default => __p('page::phrase.page_unfeatured_successfully'),
        };

        $page = $this->repository->find($id);

        return $this->success(new Detail($page), [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function approve(int $id): JsonResponse
    {
        $page = $this->repository->approve(user(), $id);

        return $this->success(new PageDetail($page), [], __p('page::phrase.approved_successfully'));
    }

    /**
     * @param UpdateAvatarRequest $request
     * @param int                 $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException|ValidationException
     */
    public function updateAvatar(UpdateAvatarRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $page    = $this->repository->find($id);
        policy_authorize(PagePolicy::class, 'uploadAvatar', $context, $page);

        $data         = $this->repository->updateAvatar($context, $id, $params);
        $data['user'] = new PageDetail($data['user']);
        LoadReduce::flush();

        return $this->success($data, [], __p('page::phrase.successfully_updated_page_avatar'));
    }

    /**
     * @param UpdateCoverRequest $request
     * @param int                $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateCover(UpdateCoverRequest $request, int $id): JsonResponse
    {
        $params       = $request->validated();
        $context      = user();
        $page         = $this->repository->find($id);
        $policyMethod = match (isset($params['image'])) {
            true    => 'uploadCover',
            default => 'editCover'
        };

        policy_authorize(PagePolicy::class, $policyMethod, $context, $page);

        $data         = $this->repository->updateCover($context, $id, $params);
        $data['user'] = new PageDetail($data['user']);

        LoadReduce::flush();

        return $this->success($data, [], __p('page::phrase.successfully_updated_page_cover'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function removeCover(int $id): JsonResponse
    {
        $this->repository->removeCover(user(), $id);

        return $this->success([], [], __p('page::phrase.page_cover_photo_removed_successfully'));
    }

    /**
     * Display the specified resource info.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function pageInfo(int $id): JsonResponse
    {
        $data = $this->repository->viewPage(user(), $id);

        return $this->success(new InfoDetail($data));
    }

    /**
     * Display a listing of the resource.
     *
     * @param MentionRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function getPageForMention(MentionRequest $request)
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->repository->getPageForMention($context, $params);

        return new PageSimpleCollection($data);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function getPrivacySettings(int $id): JsonResponse
    {
        $page = $this->repository->find($id);
        policy_authorize(PagePolicy::class, 'update', user(), $page);

        $settings = $this->privacyRepository->getProfileSettings($id);

        return $this->success($settings);
    }

    /**
     * @param Request $request
     * @param int     $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updatePrivacySettings(Request $request, int $id): JsonResponse
    {
        $context = user();
        $page    = $this->repository->find($id);

        policy_authorize(PagePolicy::class, 'update', $context, $page);

        $params = $request->all();
        UserPrivacy::validateProfileSettings($id, $params);
        $this->privacyRepository->updateUserPrivacy($id, $params);

        return $this->success(null, [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param SimilarRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function similar(SimilarRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->repository->viewSimilar($context, $params);

        return $this->success(new ItemCollection($data));
    }

    /**
     * @throws AuthenticationException
     */
    public function infoSettings(int $id): JsonResponse
    {
        $context = user();
        $data    = $this->infoSettingRepository->getInfoSettings($context, $id);

        return $this->success($data);
    }

    /**
     * @throws AuthenticationException
     */
    public function aboutSettings(int $id): JsonResponse
    {
        $context = user();

        $data = $this->infoSettingRepository->getAboutSettings($context, $id);

        return $this->success($data);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function getPageToPost(Request $request): JsonResponse
    {
        $params  = $request->all();
        $context = user();
        $data    = $this->repository->getPageToPost($context, $params);

        return $this->success($data);
    }

    /**
     * @param IndexRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function shareSuggestion(IndexRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = $owner = user();

        policy_authorize(PagePolicy::class, 'viewAny', $context);

        $data = $this->repository->viewPages($context, $owner, $params);

        return $this->success(new PageSuggestionCollection($data));
    }

    /**
     * @param UpdateProfileRequest $request
     * @param int                  $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateProfile(UpdateProfileRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $this->repository->updateProfile($context, $id, $params);

        return $this->success([], [], __p('page::phrase.page_updated.additional_information'));
    }
}
