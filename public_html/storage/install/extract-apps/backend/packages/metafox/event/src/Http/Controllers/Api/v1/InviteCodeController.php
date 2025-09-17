<?php

namespace MetaFox\Event\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use MetaFox\Event\Http\Requests\v1\InviteCode\StoreRequest;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Event\Repositories\InviteCodeRepositoryInterface;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class InviteController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group event
 */
class InviteCodeController extends ApiController
{
    public EventRepositoryInterface $eventRepository;
    public InviteCodeRepositoryInterface $codeRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        InviteCodeRepositoryInterface $codeRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->codeRepository  = $codeRepository;
    }

    /**
     * @param StoreRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $params  = $request->validated();
        $event   = $this->eventRepository->find($params['event_id']);
        $context = user();

        $code = match ((int) $params['refresh']) {
            1       => $this->codeRepository->refreshCode($context, $event),
            default => $this->codeRepository->generateCode($context, $event),
        };

        return $this->success(
            ResourceGate::asResource($code, 'item', false),
            [],
            __p('event::phrase.linked_copied_to_clipboard')
        );
    }

    /**
     * @param string $codeValue
     *
     * @return JsonResponse
     */
    public function verify(string $codeValue): JsonResponse
    {
        $code = $this->codeRepository->verifyCodeByValue($codeValue);

        return $this->success(ResourceGate::asResource($code, 'item'));
    }

    /**
     * @param string $codeValue
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function accept(string $codeValue): JsonResponse
    {
        $context = user();
        $member  = $this->codeRepository->acceptCodeByValue($context, $codeValue);

        return $this->success(ResourceGate::asResource($member, 'item'));
    }
}
