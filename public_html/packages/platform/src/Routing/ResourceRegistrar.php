<?php

namespace MetaFox\Platform\Routing;

use Illuminate\Routing\ResourceRegistrar as BaseResourceRegistrar;
use Illuminate\Routing\Route;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ResourceRegistrar extends BaseResourceRegistrar
{
    protected $resourceDefaults = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
    ];

    protected array $allResources = [
        'index', 'create', 'edit', 'delete', 'order', 'toggleActive', 'toggleDefault', 'batchActive', 'deleteAll', 'store', 'show', 'update', 'destroy',
    ];

    /**
     * @inerhitDoc
     */
    public function register($name, $controller, array $options = [])
    {
        $resources = $this->reflectControllerClass($controller, $this->allResources);

        if ($resources) {
            $this->resourceDefaults = $resources;
        }

        parent::register($name, $controller, $options);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string     $name
     * @param  string     $base
     * @param  string     $controller
     * @param  array      $options
     * @return Route|null
     */
    public function addResourceFormStore(string $name, string $base, string $controller, array $options): ?Route
    {
        $uri = $this->getResourceUri($name) . '/create';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'create', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array  $options
     * @return Route
     */
    public function addResourceFormUpdate(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . '/{' . $base . '}/create';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'create', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array  $options
     * @return Route
     */
    public function addResourceDelete(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . '/{' . $base . '}/delete';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'delete', $options);

        return $this->router->get($uri, $action);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array  $options
     * @return Route
     */
    public function addResourceDeleteAll(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . '/delete-all';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'deleteAll', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array  $options
     * @return Route
     */
    public function addResourceBatchActive(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . '/batch-active';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'batchActive', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array  $options
     * @return Route
     */
    public function addResourceOrder(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . '/order';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'order', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array  $options
     * @return Route
     */
    public function addResourceToggleActive(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . '/active/{' . $base . '}';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'toggleActive', $options);

        return $this->router->patch($uri, $action);
    }

    /**
     * Add the list method for a resourceful route.
     *
     * @param  string $name
     * @param  string $base
     * @param  string $controller
     * @param  array  $options
     * @return Route
     */
    public function addResourceToggleDefault(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . '/default/{' . $base . '}';

        unset($options['missing']);

        $action = $this->getResourceAction($name, $controller, 'toggleDefault', $options);

        return $this->router->patch($uri, $action);
    }

    private function reflectControllerClass(string $controller, array $bounds): ?array
    {
        try {
            $binding = preg_replace('/\\\\(\w+)$/m', '\\\\v1\\\\$1', $controller);
            $class = new ReflectionClass($binding);
            $methods = array_map(function (ReflectionMethod $method) {
                return $method->isPublic() ? $method->name : null;
            }, $class->getMethods());

            // correct short map.
            $methods = array_filter($bounds, function (?string $str) use ($methods) {
                return $str && in_array($str, $methods, true);
            });

            return empty($methods) ? null : $methods;
        } catch (ReflectionException) {
        }

        return null;
    }
}
