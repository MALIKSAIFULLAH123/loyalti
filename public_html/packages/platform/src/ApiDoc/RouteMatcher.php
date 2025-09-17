<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\ApiDoc;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Knuckles\Scribe\Matching\MatchedRoute;
use Knuckles\Scribe\Matching\RouteMatcherInterface;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\Http\Controllers\Api\GatewayController;
use ReflectionClass;
use ReflectionException;

/**
 * Class RouteMatcher.
 * @ignore
 */
class RouteMatcher implements RouteMatcherInterface
{
    /**
     * @param  array<mixed>  $routeRules
     * @param  string  $router
     *
     * @return array<MatchedRoute>
     * @throws ReflectionException
     */
    public function getRoutes(array $routeRules = [], string $router = 'laravel'): array
    {
        $allRoutes = RouteFacade::getRoutes();

        $matchedRoutes = [];
        $version = config('scribe.routes.match.versions', 'v1');
        $limit = 10000;

        foreach ($routeRules as $routeRule) {
            $includes = $routeRule['include'] ?? [];
            foreach ($allRoutes as $route) {
                if (!$route instanceof Route) {
                    continue;
                }

                if ($this->shouldExcludeRoute($route, $routeRule)) {
                    continue;
                }

                if (!$this->shouldIncludeRoute($route, $routeRule, $includes, false)) {
                    continue;
                }

                if (str_starts_with($route->uri, '/api/v1')) {
                    $route->uri = substr($route->uri, 9);
                }


                $action = $route->getAction();

                if (!isset($action['controller'])) {
                    continue;
                }

                $controller = $action['controller'];

                if (!is_string($controller)) {
                    continue;
                }

                [$gateway, $method] = explode('@', $controller);

                $reflect = new ReflectionClass($gateway);

                // accept only MetaFox namespace.
                if (!Str::startsWith($gateway, 'MetaFox\\')) {
                    continue;
                }

                if ($reflect->isSubclassOf(GatewayController::class)) {
                    $defaults = $reflect->getDefaultProperties();
                    if (!array_key_exists('controllers', $defaults)) {
                        continue;
                    }
                    $controllers = $defaults['controllers'];
                    // get nearest version

                    $gateway = $controllers[$version];

                    $action['controller'] = "$gateway@$method";
                    $action['uses'] = "$gateway@$method";

                    // version matches
                    $route->setAction($action);
                } elseif (!$reflect->isSubclassOf(ApiController::class)) {
                    continue;
                }
                $matchedRoutes[] = new MatchedRoute($route, $routeRule['apply'] ?? []);
                if (--$limit < 0) {
                    break;
                }
            }
        }

        uasort($matchedRoutes, function (MatchedRoute $a, MatchedRoute $b) {
            return $a->getRoute()->uri() > $b->getRoute()->uri();
        });

        return $matchedRoutes;
    }

    private function shouldIncludeRoute(
        Route $route,
        array $routeRule,
        array $mustIncludes,
        bool $usingDingoRouter
    ): bool {

        return true;
        if (Str::is($mustIncludes, $route->getName()) || Str::is($mustIncludes, $route->uri())) {
            return true;
        }

        $matchesVersion = true;
        if ($usingDingoRouter) {
            $matchesVersion = !empty(array_intersect($route->versions(), $routeRule['match']['versions'] ?? []));
        }

        $domainsToMatch = $routeRule['match']['domains'] ?? [];
        $pathsToMatch = $routeRule['match']['prefixes'] ?? [];

        return Str::is($domainsToMatch, $route->getDomain()) && Str::is($pathsToMatch, $route->uri())
            && $matchesVersion;
    }

    private function shouldExcludeRoute(Route $route, array $routeRule): bool
    {
        $excludes = $routeRule['exclude'] ?? [];

        // Exclude this package's routes
        $excludes[] = 'scribe';
        $excludes[] = 'scribe.*';

        if (Str::contains($route->uri(), 'admincp/')) {
            return true;
        }

        // Exclude Laravel Telescope routes
        if (class_exists("Laravel\Telescope\Telescope")) {
            $excludes[] = 'telescope/*';
        }

        return Str::is($excludes, $route->getName())
            || Str::is($excludes, $route->uri());
    }
}
