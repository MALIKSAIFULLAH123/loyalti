<?php

namespace MetaFox\User\Http\Controllers\Api\v1;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Requests\v1\FeatureRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\AdminLoginRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\BatchMoveRoleRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\BatchResendVerificationRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\BatchUpdateRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\DenyUserRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\IndexRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\ResendVerificationRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\UpdateCustomFieldsRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\UpdateNotificationSettingsRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\UpdateProfilePrivacyRequest;
use MetaFox\User\Http\Requests\v1\User\Admin\UpdateRequest;
use MetaFox\User\Http\Requests\v1\User\BanUserRequest;
use MetaFox\User\Http\Requests\v1\UserInactive\Admin\IndexRequest as InactiveRequest;
use MetaFox\User\Http\Requests\v1\UserInactive\Admin\ProcessMailingAllRequest;
use MetaFox\User\Http\Resources\v1\User\Admin\AccountSettingForm;
use MetaFox\User\Http\Resources\v1\User\Admin\UserItem;
use MetaFox\User\Http\Resources\v1\User\Admin\UserItemCollection as ItemCollection;
use MetaFox\User\Jobs\BatchResendVerificationJob;
use MetaFox\User\Models\User;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\UserAdminRepositoryInterface;
use MetaFox\User\Support\Facades\UserAuth;
use MetaFox\User\Support\Facades\UserVerify as UserVerifyFacade;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\User\Http\Controllers\Api\UserAdminController::$controllers.
 */

/**
 * Class UserAdminController.
 *
 * @ignore
 * @codeCoverageIgnore
 * @group user
 * @authenticated
 * @admincp
 */
class UserAdminController extends ApiController
{
    /**
     * @var UserRepositoryInterface
     */
    public UserRepositoryInterface $repository;

    /**
     * @var UserAdminRepositoryInterface
     */
    public UserAdminRepositoryInterface $adminRepository;

    public function __construct(UserRepositoryInterface $repository, UserAdminRepositoryInterface $adminRepository)
    {
        $this->repository      = $repository;
        $this->adminRepository = $adminRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection
     * @throws AuthenticationException
     * @group admin/user
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $params = $request->validated();

        $context = user();

        $data = $this->adminRepository->viewUsers($context, $params);

        return new ItemCollection($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int           $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group admin/user
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $data    = $this->adminRepository->updateUser($context, $id, $params);

        return $this->success(new UserItem($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProfilePrivacyRequest $request
     * @param int                         $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group admin/user
     */
    public function updateProfilePrivacy(UpdateProfilePrivacyRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $data    = $this->adminRepository->updateUser($context, $id, $params);

        return $this->success(new UserItem($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCustomFieldsRequest $request
     * @param int                       $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group admin/user
     */
    public function updateCustomFields(UpdateCustomFieldsRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $data    = $this->adminRepository->updateUser($context, $id, $params);

        return $this->success(new UserItem($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNotificationSettingsRequest $request
     * @param int                               $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @group admin/user
     */
    public function updateNotificationSettings(UpdateNotificationSettingsRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $data    = $this->adminRepository->updateUser($context, $id, $params);
        Artisan::call('cache:reset');

        return $this->success(new UserItem($data), [], __p('core::phrase.updated_successfully'));
    }

    /**
     * @param BatchMoveRoleRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchMoveRole(BatchMoveRoleRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $userIds = Arr::get($params, 'user_ids', []);

        foreach ($userIds as $id) {
            $user = $this->repository->find($id);

            $this->adminRepository->moveRole(user(), $user, Arr::get($params, 'role_id'));
        }

        Artisan::call('cache:reset');

        return $this->success([], [], __p('user::phrase.user_s_successfully_moved_to_new_group'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @group admin/user
     */
    public function destroy(int $id): JsonResponse
    {
        $context  = user();
        $resource = $this->repository->find($id);

        policy_authorize(UserPolicy::class, 'delete', $context, $resource);

        $this->repository->deleteUser($context, $id);

        return $this->success([
            'id' => $id,
        ], [], __p('user::phrase.user_successfully_deleted'));
    }

    /**
     * @param AdminLoginRequest $request
     *
     * @return mixed
     * @group admin/user
     */
    public function login(AdminLoginRequest $request)
    {
        $username = $request->validated('username', '');
        $password = $request->validated('password', '');

        $response = UserAuth::login($request);

        if (is_array($response) && Arr::exists($response, 'redirect_url')) {
            return [
                'data' => $response,
            ];
        }

        $user = $this->repository->findAndValidateForAuth($username, $password);

        app('events')->dispatch('user.admin_signed_in', [$user]);

        return $response;
    }

    /**
     * View editing form.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function edit(int $id): JsonResponse
    {
        $item = $this->repository->find($id);

        return $this->success(new AccountSettingForm($item));
    }

    /**
     * @throws AuthenticationException
     */
    public function approve(int $id): JsonResponse
    {
        $user = $this->repository->find($id);
        if ($user instanceof User && $user->isApproved()) {
            $message = json_encode([
                'title'   => __p('user::phrase.user_already_approved_title'),
                'message' => __p('user::phrase.user_already_approved'),
            ]);
            abort(403, $message);
        }

        $this->repository->approve(user(), $id);

        return $this->success([
            'id'         => $id,
            'is_pending' => 0,
        ], [], __p('user::phrase.user_has_been_approved'));
    }

    /**
     * @param BatchUpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchApprove(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $userIds = Arr::get($params, 'id', []);

        foreach ($userIds as $id) {
            $user = $this->repository->find($id);

            if (!$user->hasVerified()) {
                continue;
            }

            if ($user instanceof User && !$user->isApproved()) {
                $this->repository->approve(user(), $id);
            }
        }

        return $this->success([], [], __p('user::phrase.user_s_successfully_approved'));
    }

    /**
     * @param BatchUpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchDelete(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $userIds = Arr::get($params, 'id', []);
        $context = user();

        foreach ($userIds as $id) {
            $user = $this->repository->find($id);

            if (!policy_check(UserPolicy::class, 'delete', $context, $user)) {
                continue;
            }

            $this->repository->deleteUser($context, $id);
        }

        return $this->success([], [], __p('user::phrase.user_s_successfully_deleted'));
    }

    /**
     * @param FeatureRequest $request
     * @param int            $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function feature(FeatureRequest $request, int $id): JsonResponse
    {
        $params  = $request->validated();
        $feature = $params['feature'];
        $this->repository->feature(user(), $id, $feature);

        $message = __p('user::phrase.user_featured_successfully');
        if (!$feature) {
            $message = __p('user::phrase.user_unfeatured_successfully');
        }

        return $this->success([
            'id'          => $id,
            'is_featured' => (int) $feature,
        ], [], $message);
    }

    /**
     * @param BanUserRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function banUser(BanUserRequest $request): JsonResponse
    {
        $params = $request->validated();

        /** @var User $owner */
        $owner = User::query()->find($params['user_id']);

        $reason = !empty($params['reason']) ? $params['reason'] : null;

        policy_authorize(UserPolicy::class, 'banUser', user(), $owner);

        $this->repository->banUser(user(), $owner, $params['day'], $params['return_user_group'], $reason);

        Artisan::call('cache:reset');

        return $this->success(new UserItem($owner), [], __p('user::phrase.user_was_banned_successfully'));
    }

    /**
     * @param BatchUpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchBanUser(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $userIds = Arr::get($params, 'id', []);

        foreach ($userIds as $id) {
            $owner = $this->repository->find($id);

            policy_authorize(UserPolicy::class, 'banUser', user(), $owner);

            $this->repository->banUser(user(), $owner);
        }

        Artisan::call('cache:reset');

        return $this->success([], [], __p('user::phrase.user_s_successfully_banned'));
    }

    /**
     * @param BatchUpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function batchUnBanUser(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $userIds = Arr::get($params, 'id', []);

        foreach ($userIds as $id) {
            $owner = $this->repository->find($id);

            $this->repository->removeBanUser(user(), $owner);
        }

        return $this->success([], [], __p('user::phrase.user_s_successfully_un_banned'));
    }

    /**
     * @param BatchUpdateRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchVerify(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $userIds = Arr::get($params, 'id', []);

        foreach ($userIds as $id) {
            $owner = $this->repository->find($id);

            $this->adminRepository->verifyUser(user(), $owner);
        }

        return $this->success([], [], __p('user::phrase.user_s_verified'));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function verifyUser(int $id): JsonResponse
    {
        $owner = $this->repository->find($id);

        $this->adminRepository->verifyUser(user(), $owner);

        return $this->success([], [], __p('user::phrase.user_s_verified'));
    }

    /**
     * @param BatchResendVerificationRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function batchResendVerification(BatchResendVerificationRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();
        $action  = Arr::get($params, 'action');

        BatchResendVerificationJob::dispatch($context, $params);

        return $this->success([], [], __p('user::phrase.verification_services_sent', ['action' => $action]));
    }

    /**
     * @param int $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function resendVerification(ResendVerificationRequest $request, int $id): JsonResponse
    {
        $params          = $request->validated();
        $context         = user();
        $action          = Arr::get($params, 'action');
        $verifiableField = UserVerifyFacade::getVerifiableField($action);
        $user            = $this->repository->find($id);

        policy_authorize(UserPolicy::class, 'manage', $context, $user);

        UserVerifyFacade::admin($action)->resend($user, $user->{$verifiableField});

        return $this->success([], [], __p('user::phrase.verification_services_sent', ['action' => $action]));
    }

    /**
     * Un-ban user.
     *
     * @param int $userId
     *
     * @return JsonResponse
     * @throws AuthorizationException|AuthenticationException
     * @group user
     * @authenticated
     */
    public function unBanUser(int $userId): JsonResponse
    {
        /** @var User $owner */
        $owner = User::query()->findOrFail($userId);

        $this->repository->removeBanUser(user(), $owner);

        return $this->success(new UserItem($owner), [], __p('user::phrase.user_was_removed_banned_successfully'));
    }

    /**
     * @param DenyUserRequest $request
     * @param int             $id
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function denyUser(DenyUserRequest $request, int $id): JsonResponse
    {
        $params = $request->validated();
        $user   = $this->repository->find($id);

        if ($user instanceof User) {
            if ($user->isApproved()) {
                $message = json_encode([
                    'title'   => __p('user::phrase.user_already_approved_title'),
                    'message' => __p('user::phrase.user_already_approved'),
                ]);
                abort(403, $message);
            }

            if ($user->isNotApproved()) {
                $message = json_encode([
                    'title'   => __p('user::phrase.not_approved'),
                    'message' => __p('user::phrase.user_has_been_denied'),
                ]);
                abort(403, $message);
            }
        }

        $this->repository->denyUser(user(), $id, $params);

        return $this->success([
            'id' => $id,
        ], [], __p('user::phrase.user_has_been_denied'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param InactiveRequest $request
     *
     * @return ItemCollection
     * @throws AuthenticationException
     * @group admin/user
     */
    public function inactive(InactiveRequest $request): ItemCollection
    {
        $params  = $request->validated();
        $context = user();

        $data = $this->adminRepository->viewUsers($context, $params);

        return new ItemCollection($data);
    }

    public function processMailing(int $id): JsonResponse
    {
        $context = user();
        $owner   = $this->adminRepository->find($id);

        $this->adminRepository->processMailing($context, $owner);

        return $this->success(new UserItem($owner), [], __p('user::phrase.user_was_processed_mailing_job_successfully'));
    }

    public function batchProcessMailing(BatchUpdateRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $userIds = Arr::get($params, 'id', []);
        $context = user();

        $this->adminRepository->batchProcessMailing($context, $userIds);

        return $this->success([], [], __p('user::phrase.user_s_was_processed_mailing_job_successfully'));
    }

    public function processMailingAll(ProcessMailingAllRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $context = user();

        $this->adminRepository->processMailingAll($context, $params);

        return $this->success([], [], __p('user::phrase.successfully_add_mailing_job_to_all_inactive_users', ['day' => $params['day']]));
    }

    public function logoutAllUser(Request $request): JsonResponse
    {
        $context = user();

        $this->adminRepository->logoutAllUsers($context, []);

        Artisan::call('cache:reset');

        return $this->success([], [], __p('user::phrase.logging_out_all_users_successfully'));
    }
}
