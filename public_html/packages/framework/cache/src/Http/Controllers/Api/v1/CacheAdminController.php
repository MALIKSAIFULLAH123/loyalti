<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Cache\Http\Controllers\Api\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Platform\Http\Controllers\Api\ApiController;

/**
 * Class CacheAdminController.
 * @ignore
 * @authenticated
 * @group   admin/cache
 */
class CacheAdminController extends ApiController
{
    public function clearCache(Request $request): JsonResponse
    {
        $optimize = $request->get('optimize');

        if ($optimize) {
            Artisan::call('cache:reset');
            Artisan::call('optimize');
            Artisan::call('queue:restart');
        } else {
            Artisan::call('cache:reset');
        }

        return $this->success(['id' => 1], [], __p('cache::phrase.cache_is_cleared_successfully'));
    }
}
