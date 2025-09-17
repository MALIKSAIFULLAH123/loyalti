<?php

namespace MetaFox\SEO\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;

/**
 * | --------------------------------------------------------------------------
 * |  Api Controller
 * | --------------------------------------------------------------------------
 * |
 * | stub: /packages/controllers/api_controller.stub
 * | Assign this class in $controllers of
 * | @link \MetaFox\Core\Http\Controllers\Api\MetaController::$controllers.
 */

/**
 * Class MetaController.
 * @codeCoverageIgnore
 * @ignore
 */
class MetaController extends ApiController
{
    /**
     * @var MetaRepositoryInterface
     */
    private MetaRepositoryInterface $repository;

    /**
     * MetaController Constructor.
     *
     * @param MetaRepositoryInterface $repository
     */
    public function __construct(MetaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * View item.
     * @urlParam metaName string required Page meta name. Example: blog.browse.home
     * @param  string  $name
     * @param  Request  $request
     * @return JsonResponse
     */
    public function showMetaName(string $name, Request $request): JsonResponse
    {
        $resolution  = $request->get('resolution', 'web');

        $data = resolve(MetaRepositoryInterface::class)
            ->getSeoSharingData($resolution, $name, null, null);

        return $this->success($data);
    }

    public function showMeta(Request $request)
    {
        $url  = $request->get('url');
        $path = 'sharing/' . trim($url, '/');

        defined('MFOX_SHARING_RETRY_ARRAY') or define('MFOX_SHARING_RETRY_ARRAY', true);

        return Route::dispatch(Request::create($path, 'GET', []));
    }
}
