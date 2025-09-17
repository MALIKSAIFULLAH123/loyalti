<?php

namespace MetaFox\ChatPlus\Http\Controllers\Api\v1;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use MetaFox\ChatPlus\Http\Resources\v1\User\JobItemCollection;
use MetaFox\ChatPlus\Http\Resources\v1\User\UserItem;
use MetaFox\ChatPlus\Http\Resources\v1\User\UserItemCollection;
use MetaFox\ChatPlus\Repositories\ChatServerInterface;
use MetaFox\ChatPlus\Repositories\JobRepositoryInterface;
use MetaFox\ChatPlus\Support\Traits\ChatplusTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\Eloquent\UserRepository;

/**
 * Class ChatPlusController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group chatplus
 * @subgroup ChatPlus
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ChatPlusController extends ApiController
{
    use ChatplusTrait;
    /**
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepository;

    /**
     * @var JobRepositoryInterface
     */
    private JobRepositoryInterface $jobRepository;

    /**
     * @var ChatServerInterface
     */
    private ChatServerInterface $chatServer;

    /**
     * ChatPlusController constructor.
     *
     * @param UserRepositoryInterface $userRepository
     * @param JobRepositoryInterface  $jobRepository
     * @param ChatServerInterface     $chatServer
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        JobRepositoryInterface $jobRepository,
        ChatServerInterface $chatServer
    ) {
        $this->userRepository = $userRepository;
        $this->jobRepository  = $jobRepository;
        $this->chatServer     = $chatServer;
    }

    /**
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function me(): JsonResponse
    {
        $user = user();

        return $this->success(new UserItem($user));
    }

    /**
     * @param Request     $request
     * @param string|null $type
     * @param string|null $query
     *
     * @return JsonResponse
     */
    public function checkUser(Request $request, ?string $type = null, ?string $query = null): JsonResponse
    {
        if (!$this->validateRequest($request)) {
            return $this->error(__p('core::phrase.content_is_not_available'), 403);
        }
        switch ($type) {
            case 'id':
            case 'user_id':
            case 'phpfoxId':
            case 'metafoxId':
            case 'phpfoxUserId':
            case 'metafoxUserId':
                $user = User::query()->where('id', $query)->first();
                break;
            case 'email':
                $user = User::query()->where('email', '=', $query)->first();
                break;
            case 'username':
            case 'user_name':
                $user = User::query()->where('user_name', '=', $query)->first();
                break;
        }

        if (empty($user)) {
            return $this->error(
                __p('core::phrase.the_entity_name_you_are_looking_for_can_not_be_found', ['entity_name' => 'user']),
                403
            );
        }

        return $this->success(new UserItem($user));
    }

    /**
     * @param  Request      $request
     * @param  string|null  $type
     * @param  string|null  $idFrom
     * @param  string|null  $idTo
     * @return JsonResponse
     */
    public function canCreateDirectMessage(
        Request $request,
        ?string $type = null,
        ?string $idFrom = null,
        ?string $idTo = null
    ): JsonResponse {
        /** @var UserRepository $userRepo */
        $userRepo = resolve(UserRepositoryInterface::class);
        if ($type === 'username') {
            $userFrom = $userRepo->where(['user_name' => $idFrom])->first();
            $userTo   = $userRepo->where(['user_name' => $idTo])->first();
        } else {
            $userFrom = $userRepo->where(['id' => (int) $idFrom])->first();
            $userTo   = $userRepo->where(['id' => (int) $idTo])->first();
        }

        return $this->success([
            'canCreateDirectMessage' => $userFrom && $userTo
                && ($userFrom->id == $userTo->id || $this->canMessage($userFrom, $userTo)),
        ]);
    }

    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function spotlight(Request $request): JsonResponse
    {
        if (!$this->validateRequest($request)) {
            return $this->error(__p('core::phrase.content_is_not_available'), 403);
        }
        $users = $this->userRepository->all();

        return $this->success(new UserItemCollection($users));
    }

    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function settings(Request $request): JsonResponse
    {
        if (!$this->validateRequest($request)) {
            return $this->error(__p('core::phrase.content_is_not_available'), 403);
        }
        $data = $this->chatServer->getSettings(false, true);

        return $this->success($data);
    }

    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function prefetchUsers(Request $request): JsonResponse
    {
        if (!$this->validateRequest($request)) {
            return $this->error(__p('core::phrase.content_is_not_available'), 403);
        }
        $data = $this->chatServer->prefetchUsers($request->all());

        return $this->success($data);
    }

    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function loadJobs(Request $request): JsonResponse
    {
        if (!$this->validateRequest($request)) {
            return $this->error(__p('core::phrase.content_is_not_available'), 403);
        }
        $jobs = $this->chatServer->loadJobs($request->all());

        return $this->success(new JobItemCollection($jobs));
    }

    /**
     * @param  Request      $request
     * @return JsonResponse
     */
    public function exportUsers(Request $request): JsonResponse
    {
        $this->chatServer->syncUsers();

        return $this->success([], [], __p('chatplus::phrase.sync_user_success_description'));
    }

    public function roomsUpload(Request $request, ?string $roomId = null)
    {
        $requestData = $request->all();
        $validator   = Validator::make($request->all(), [
            'userId' => ['required', 'string'],
            'roomId' => ['required', 'string'],
            'token'  => ['required', 'string'],
            'msg'    => ['string'],
            'file'   => ['required', 'array'],
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $api         = rtrim(Settings::get('chatplus.server'), '/') . "/api/v1/rooms.upload/$roomId";
        $data        = [];
        $data['msg'] = $requestData['msg'] ?? '';
        /**
         * @var int          $iKey
         * @var UploadedFile $file
         */
        foreach ($requestData['file'] as $iKey => $file) {
            if ($file->getError() == UPLOAD_ERR_OK) {
                $data['file' . $iKey] = curl_file_create(realpath($file->getPathName()), $file->getMimeType(), $file->getClientOriginalName());
            }
        }

        $http_headers = [
            'X-Auth-Token:' . $requestData['token'],
            'X-User-Id: ' . $requestData['userId'],
        ];

        $ch = curl_init($api);

        curl_setopt($ch, CURLOPT_URL, $api);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $content = curl_exec($ch);
        if (empty($content) || curl_error($ch)) {
            curl_close($ch);

            return $this->error();
        }
        $content = json_decode($content, true);
        curl_close($ch);

        return empty($content['success']) ? $this->error($content['error'] ?? ($content['message'] ?? '')) : $this->success([]);
    }

    public function fetchLink(Request $request)
    {
        if (!$this->validateRequest($request)) {
            return $this->error(__p('core::phrase.content_is_not_available'), 403);
        }
        try {
            $data = $this->chatServer->getInternalUrlMetadata($request->get('url', ''));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }

        return $this->success($data);
    }
}
