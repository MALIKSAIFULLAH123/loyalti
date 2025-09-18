<?php

namespace MetaFox\Video\Http\Controllers\Api\v1;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Video\Contracts\ProviderManagerInterface;
use MetaFox\Video\Http\Requests\v1\VideoService\Admin\IndexRequest;
use MetaFox\Video\Http\Resources\v1\VideoService\Admin\VideoServiceItem as Item;
use MetaFox\Video\Http\Resources\v1\VideoService\Admin\VideoServiceItemCollection as ItemCollection;

/**
 * Class ServiceAdminController.
 * @ignore
 * @codeCoverageIgnore
 * @authenticated
 * @group admincp/video
 */
class ServiceAdminController extends ApiController
{
    /**
     * @param ProviderManagerInterface $providerManager
     */
    public function __construct(protected ProviderManagerInterface $providerManager)
    {
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

        $data = $this->providerManager->viewServices($context, $params);

        return new ItemCollection($data);
    }
}
