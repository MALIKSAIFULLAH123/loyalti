<?php

namespace MetaFox\SEO\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SharingController extends Controller
{
    /**
     * @throws AuthenticationException
     */
    public function fallback(Request $request, $url = '/')
    {
        $result = $this->getParseRoute($url);

        if ($result) {
            // prevent recursive request.
            defined('MFOX_SHARING_RETRY') or define('MFOX_SHARING_RETRY', true);

            $path = sprintf('sharing' . $result['path']);

            $request = Request::create($path, 'GET', []);

            return Route::dispatch($request);
        }

        $resolution = $request->get('resolution', 'web');

        return seo_sharing_view($resolution, $url);
    }

    private function getParseRoute(string $url): ?array
    {
        // prevent recursive request.
        if (defined('MFOX_SHARING_RETRY')) {
            return null;
        }

        $result = app('events')->dispatch('parseSharingRoute', [$url], true);

        if (!empty($result)) {
            return $result;
        }

        return app('events')->dispatch('parseRoute', [$url], true);
    }
}
