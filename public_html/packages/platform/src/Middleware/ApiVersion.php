<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;

class ApiVersion
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('access_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->get('access_token'));
        }

        /* @link \MetaFox\Platform\ApiResourceManager::setVersion */
        $ver = $request->header('X-API-Version');

        if (!$ver) {
            $ver = $request->route('ver', MetaFoxConstant::DEFAULT_API_VERSION);
        }

        ResourceGate::setVersion($ver);

        $request->route()->forgetParameter('ver');

        URL::defaults(['ver' => $ver]);

        return $next($request);
    }
}
