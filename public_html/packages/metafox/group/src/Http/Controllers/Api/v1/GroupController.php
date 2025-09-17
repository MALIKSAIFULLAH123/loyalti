<?php

namespace MetaFox\Group\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use MetaFox\Group\Http\Requests\v1\Group\AnswerQuestionConfirmationRequest;
use MetaFox\Group\Http\Requests\v1\Group\IndexRequest;
use MetaFox\Group\Http\Requests\v1\Group\MentionRequest;
use MetaFox\Group\Http\Requests\v1\Group\RuleConfirmationRequest;
use MetaFox\Group\Http\Requests\v1\Group\ShowRequest;
use MetaFox\Group\Http\Requests\v1\Group\SimilarRequest;
use MetaFox\Group\Http\Requests\v1\Group\StoreRequest;
use MetaFox\Group\Http\Requests\v1\Group\UpdateAvatarRequest;
use MetaFox\Group\Http\Requests\v1\Group\UpdateCoverRequest;
use MetaFox\Group\Http\Requests\v1\Group\UpdateProfileRequest;
use MetaFox\Group\Http\Requests\v1\Group\UpdateRequest;
use MetaFox\Group\Http\Resources\v1\Group\GroupDetail;
use MetaFox\Group\Http\Resources\v1\Group\GroupDetail as Detail;
use MetaFox\Group\Http\Resources\v1\Group\GroupInfo;
use MetaFox\Group\Http\Resources\v1\Group\GroupItemCollection as ItemCollection;
use MetaFox\Group\Http\Resources\v1\Group\GroupSimpleCollection;
use MetaFox\Group\Http\Resources\v1\Group\GroupSuggestionCollection;
use MetaFox\Group\Models\Group;
use MetaFox\Group\Models\GroupInviteCode;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupChangePrivacyRepositoryInterface;
use MetaFox\Group\Repositories\GroupInviteCodeRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Repositories\InfoSettingRepositoryInterface;
use MetaFox\Group\Repositories\InviteRepositoryInterface;
use MetaFox\Platform\Exceptions\PermissionDeniedException;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\Platform\Http\Requests\v1\PendingModeRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorInFeedRequest;
use MetaFox\Platform\Http\Requests\v1\SponsorRequest;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\User\Support\Facades\UserPrivacy;
use MetaFox\User\Support\Facades\UserValue;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class GroupController.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @ignore
 * @codeCoverageIgnore
 * @group group
 * @authenticated
 */
class GroupController extends ApiController
{
    public function __construct(
        protected GroupRepositoryInterface              $repository,
        protected UserPrivacyRepositoryInterface        $privacyRepository,
        protected GroupChangePrivacyRepositoryInterface $groupChangePrivacy,
        protected GroupInviteCodeRepositoryInterface    $codeRepository,
        protected InviteRepositoryInterface             $inviteRepository,
        protected InfoSettingRepositoryInterface        $infoSettingRepository,
    ) {}

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
            if (!policy_check(GroupPolicy::class, 'viewOnProfilePage', $context, $owner)) {
                throw new AuthorizationException();
            }
        }

        policy_authorize(GroupPolicy::class, 'viewAny', $context, $owner);

        $data = match ($view) {
            Browse::VIEW_SPONSOR => $this->repository->getRandomSponsoredItems($context, $limit ?? 4),
            default              => $this->repository->viewGroups($context, $owner, $params),
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
        $context = $owner = user();
        $params  = $request->validated();

        app('flood')->checkFloodControlWhenCreateItem(user(), Group::ENTITY_TYPE);
        app('quota')->checkQuotaControlWhenCreateItem(user(), Group::ENTITY_TYPE);

        if ($params['owner_id'] > 0) {
            if ($context->entityId() != $params['owner_id']) {
                $owner = UserEntity::getById($params['owner_id'])->detail;
            }
        }
        $group = $this->repository->createGroup($context, $owner, $params);

        $message = __p(
            'core::phrase.resource_create_success',
            ['resource_name' => __p('group::phrase.group')]
        );

        if (!$group->isApproved()) {
            $message = __p('core::phrase.thanks_for_your_item_for_approval');
        }

        $meta = $this->repository->askingForPurchasingSponsorship($context, $group);

        return $this->success(new Detail($group), $meta, $message);
    }

    /**
     * Display the specified resource.
     *
     * @param ShowRequest $request
     * @param int         $id
     *
     * @return Detail
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws ValidatorException
     */
    public function show(ShowRequest $request, int $id): Detail
    {
        $context  = user();
        $params   = $request->validated();
        $group    = $this->repository->getGroup($id);
        $code     = Arr::get($params, 'invite_code', null);
        $resource = new Detail($group);

        if ($code !== null && !$group->isMember($context)) {
            $inviteCode = $this->codeRepository->getCodeByValue($code, GroupInviteCode::STATUS_ACTIVE);
            $resource->setInviteCode($code);

            if (null == $inviteCode) {
                throw new AuthorizationException();
            }

            $code = $inviteCode->code;

            policy_authorize(GroupPolicy::class, 'invite', $inviteCode->user, $group);

            if (!$context->isGuest()) {
                $this->inviteRepository->inviteFriend($inviteCode->user, $group, $context, $inviteCode);
            }
        }

        policy_authorize(GroupPolicy::class, 'view', $context, $group, $code);

        return $resource;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $data    = $this->repository->updateGroup(user(), $id, $params);
        $key     = array_key_first($params);
        $message = __p("group::phrase.group_updated.$key");

        if (Arr::has($params, 'privacy_type')) {
            $numberDays = Settings::get('group.number_days_expiration_change_privacy');
            $message    = __p("group::phrase.group_updated.$key", ['numbers' => $numberDays]);
        }

        unset($params['location_latitude'], $params['location_longitude']);
        if (count($params) > 1) {
            $message = __p('group::phrase.group_updated.info');
        }

        return $this->success(new Detail($data), [], $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException|AuthorizationException
     */
    public function destroy(int $id): JsonResponse
    {
        $group        = $this->repository->find($id);
        $policyMethod = $group->isApproved() ? 'delete' : 'decline';

        policy_authorize(GroupPolicy::class, $policyMethod, user(), $group);
        $this->repository->deleteGroup(user(), $id);

        return $this->success([
            'id' => $id,
        ], [], __p('group::phrase.successfully_deleted_the_group'));
    }

    /**
     * @param SponsorRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     */
    public function sponsor(SponsorRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $params['sponsor'];

        $this->repository->sponsor(user(), $id, $sponsor);

        $group = $this->repository->find($id);

        $isSponsor = (bool) $sponsor;

        $isPendingSponsor = $isSponsor && !$group->is_sponsor;

        $message = $isPendingSponsor ? 'core::phrase.resource_sponsored_successfully_please_waiting_for_approval' : ($isSponsor ? 'core::phrase.resource_sponsored_successfully' : 'core::phrase.resource_unsponsored_successfully');

        $message = __p($message, ['resource_name' => __p('group::phrase.sponsor_setting_group')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($group), [], __p($message));
    }

    public function sponsorInFeed(SponsorInFeedRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $sponsor = $params['sponsor'];

        $this->repository->sponsorInFeed(user(), $id, $sponsor);

        $isSponsor        = (bool) $sponsor;
        $group            = $this->repository->find($id);
        $isPendingSponsor = $isSponsor && !$group->sponsor_in_feed;
        $message          = $isPendingSponsor
            ? 'core::phrase.resource_sponsored_in_feed_successfully_please_waiting_for_approval'
            : ($isSponsor
                ? 'core::phrase.resource_sponsored_in_feed_successfully'
                : 'core::phrase.resource_unsponsored_in_feed_successfully');

        $message = __p($message, ['resource_name' => __p('group::phrase.sponsor_setting_group')]);

        if ($isSponsor) {
            $this->navigate('advertise/sponsor');
        }

        return $this->success(new Detail($group), [], $message);
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
            1       => __p('group::phrase.group_featured_successfully'),
            default => __p('group::phrase.group_unfeatured_successfully'),
        };

        $group = $this->repository->find($id);

        return $this->success(new Detail($group), [], $message);
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function approve(int $id): JsonResponse
    {
        $group = $this->repository->approve(user(), $id);

        return $this->success(new GroupDetail($group), [], __p('group::phrase.group_has_been_approved'));
    }

    /**
     * @param UpdateAvatarRequest $request
     * @param int                 $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updateAvatar(UpdateAvatarRequest $request, int $id): JsonResponse
    {
        $params      = $request->validated();
        $imageBase46 = $params['image'];

        $this->repository->updateAvatar(user(), $id, $imageBase46);

        return $this->success([], [], __p('group::phrase.successfully_updated_group_avatar'));
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
        $group        = $this->repository->find($id);
        $policyMethod = match (isset($attributes['image'])) {
            true    => 'uploadCover',
            default => 'editCover'
        };

        policy_authorize(GroupPolicy::class, $policyMethod, $context, $group);

        $data         = $this->repository->updateCover($context, $id, $params);
        $data['user'] = new GroupDetail($data['user']);

        LoadReduce::flush();

        return $this->success($data, [], __p('group::phrase.successfully_updated_group_cover'));
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

        return $this->success([], [], __p('group::phrase.group_cover_photo_removed_successfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param ShowRequest $request
     * @param int         $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws AuthorizationException
     */
    public function groupInfo(ShowRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $code   = null;
        if (isset($params['invite_code'])) {
            $code = $this->codeRepository->getCodeByValue($params['invite_code'], GroupInviteCode::STATUS_ACTIVE);
        }
        $group = $this->repository->viewGroup(user(), $id, $code);

        return $this->success(new GroupInfo($group));
    }

    /**
     * Display a listing of the resource.
     *
     * @param MentionRequest $request
     *
     * @return JsonResource
     * @throws AuthenticationException
     */
    public function getGroupForMention(MentionRequest $request)
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->repository->getGroupForMention($context, $params);

        return new GroupSimpleCollection($data);
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
        $context = user();
        $group   = $this->repository->find($id);
        policy_authorize(GroupPolicy::class, 'update', $context, $group);
        $settings = $this->privacyRepository->getProfileSettings($id);

        return $this->success($settings);
    }

    /**
     * @throws AuthenticationException|AuthorizationException
     */
    public function updatePrivacySettings(Request $request, int $id): JsonResponse
    {
        $context = user();
        $group   = $this->repository->find($id);

        policy_authorize(GroupPolicy::class, 'update', $context, $group);

        $params = $request->all();
        UserPrivacy::validateProfileSettings($id, $params);
        $this->privacyRepository->updateUserPrivacy($id, $params);

        return $this->success(null, [], __p('group::phrase.privacy_settings_has_been_updated_successfully'));
    }

    /**
     * @param PendingModeRequest $request
     * @param int                $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function updatePendingMode(PendingModeRequest $request, int $id): JsonResponse
    {
        $params      = $request->validated();
        $pendingMode = (int) $params['pending_mode'];
        $this->repository->updatePendingMode(user(), $id, $pendingMode);

        return $this->success(
            ['pending_mode' => (bool) $pendingMode],
            [],
            __p('group::phrase.setting_successfully_updated')
        );
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function getModerationRights(int $id): JsonResponse
    {
        $owner = UserEntity::getById($id)->detail;
        policy_authorize(GroupPolicy::class, 'manageGroup', user(), $owner);

        /** @var Group $owner */
        $settings = [];
        $options  = [
            ['label' => __p('core::phrase.yes'), 'value' => true],
            ['label' => __p('core::phrase.no'), 'value' => false],
        ];

        foreach (UserValue::getUserValueSettings($owner) as $name => $setting) {
            $setting['value'] = (bool) $setting['value'];
            if ($owner->isPublicPrivacy() && $name == 'approve_or_deny_membership_request') {
                continue;
            }
            $settings[] = array_merge($setting, [
                'var_name' => $name,
                'options'  => $options,
            ]);
        }

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
    public function updateModerationRights(Request $request, int $id): JsonResponse
    {
        $owner = UserEntity::getById($id)->detail;
        policy_authorize(GroupPolicy::class, 'manageGroup', user(), $owner);

        UserValue::updateUserValueSetting($owner, $request->all());

        return $this->success([], [], __p('group::phrase.setting_successfully_updated'));
    }

    /**
     * @param RuleConfirmationRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function confirmRule(RuleConfirmationRequest $request): JsonResponse
    {
        $params   = $request->validated();
        $context  = user();
        $resource = $this->repository->updateRuleConfirmation(
            $context,
            $params['group_id'],
            $params['is_rule_confirmation']
        );

        return $this->success(new GroupDetail($resource), [], __p('group::phrase.setting_successfully_updated'));
    }

    /**
     * @param AnswerQuestionConfirmationRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function confirmAnswerMembershipQuestion(AnswerQuestionConfirmationRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $resource = $this->repository->updateAnswerMembershipQuestion(
            $context,
            $params['group_id'],
            $params['is_answer_membership_question']
        );

        return $this->success(new GroupDetail($resource), [], __p('group::phrase.setting_successfully_updated'));
    }

    /**
     * @throws AuthenticationException
     */
    public function cancelRequestChangePrivacy(int $id): JsonResponse
    {
        $context  = user();
        $resource = $this->groupChangePrivacy->cancelRequest($context, $id);
        if (!$resource) {
            $this->error();
        }
        $group = $this->repository->find($id);

        return $this->success(new Detail($group), [], __p('group::phrase.cancel_change_privacy_successfully'));
    }

    /**
     * @throws AuthenticationException
     */
    public function infoSettings(int $id): JsonResponse
    {
        $context = user();
        $data    = $this->infoSettingRepository->getInfoSettingsGroup($context, $id);

        return $this->success($data);
    }

    /**
     * @throws AuthenticationException
     */
    public function aboutSettings(int $id): JsonResponse
    {
        $context = user();
        $data    = $this->infoSettingRepository->getAboutSettingsGroup($context, $id);

        return $this->success($data);
    }

    public function getGroupToPost(Request $request): JsonResponse
    {
        $params  = $request->all();
        $context = user();
        $data    = $this->repository->getGroupToPost($context, $params);

        return $this->success($data);
    }

    /**
     * @param SimilarRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function similar(SimilarRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->repository->viewSimilar($context, $params);

        return $this->success(new ItemCollection($data));
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

        policy_authorize(GroupPolicy::class, 'viewAny', $context);

        $data = $this->repository->viewGroups($context, $owner, $params);

        return $this->success(new GroupSuggestionCollection($data));
    }

    /**
     * @throws AuthenticationException
     */
    public function updateProfile(UpdateProfileRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();

        $context = user();

        $this->repository->updateProfile($context, $id, $params);

        return $this->success([], [], __p('group::phrase.group_updated.additional_information'));
    }
}
