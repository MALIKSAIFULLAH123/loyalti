<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Middleware;

use Closure;

/**
 * Excluding arguments from routes, it's helpful when combined to use auto binding arguments.
 *
 * Class FilterArguments
 */
class FilterArguments
{
    public function handle($request, Closure $next, ...$arguments)
    {
        if ($arguments) {
            foreach ($arguments as $argument) {
                $request->route()->forgetParameter($argument);
            }
        }

        return $next($request);
    }
}
