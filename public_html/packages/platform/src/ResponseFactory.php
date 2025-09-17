<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\ResponseFactory as RoutingResponseFactory;

/**
 * Class UserRole.
 */
class ResponseFactory extends RoutingResponseFactory
{
    /**
     * Create a new JSON response instance.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return JsonResponse
     */
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        return parent::json($data, $status, $headers, $options | JSON_INVALID_UTF8_IGNORE);
    }
}
