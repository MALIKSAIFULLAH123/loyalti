<?php

namespace MetaFox\LiveStreaming\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MetaFox\Core\Constants;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\LiveStreaming\Http\Requests\v1\StreamingService\Admin\IndexRequest;
use MetaFox\LiveStreaming\Http\Resources\v1\StreamingService\Admin\StreamingServiceItem as Item;
use MetaFox\LiveStreaming\Http\Resources\v1\StreamingService\Admin\StreamingServiceItemCollection as ItemCollection;
use MetaFox\LiveStreaming\Repositories\StreamingServiceRepositoryInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class ServiceAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group admincp/livestreaming
 */
class StreamingServiceAdminController extends ApiController
{
    /**
     * @var StreamingServiceRepositoryInterface
     */
    public StreamingServiceRepositoryInterface $repository;

    /**
     * @param StreamingServiceRepositoryInterface $repository
     */
    public function __construct(StreamingServiceRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Browse category.
     *
     * @param IndexRequest $request
     *
     * @return ItemCollection<Item>
     * @throws AuthenticationException
     */
    public function index(IndexRequest $request): ItemCollection
    {
        $context = user();
        $params  = $request->validated();

        $data = $this->repository->viewServices($context, $params);

        return new ItemCollection($data);
    }

    /**
     * View editing form.
     *
     * @param  Request      $request
     * @param  int          $service
     * @return JsonResponse
     */
    public function edit(Request $request, int $service): JsonResponse
    {
        $streamingService = $this->repository->find($service);
        [,$driver]        = resolve(DriverRepositoryInterface::class)->loadDriver(
            Constants::DRIVER_TYPE_FORM_SETTINGS,
            sprintf('livestreaming.%s', $streamingService->driver),
            'admin'
        );

        $form = resolve($driver);
        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form);
    }
}
