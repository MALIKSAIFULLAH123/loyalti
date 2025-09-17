<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform\Http\Controllers\Api;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;
use Illuminate\Routing\ControllerDispatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Exceptions\NotFoundApiVersionException;
use MetaFox\Platform\Facades\LoadReduce;
use MetaFox\Platform\Facades\Profiling;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\Traits\Fox4JsonResponse;

class GatewayController extends Controller
{
    use Fox4JsonResponse;

    /** @var Request */
    protected $request;

    /** @var ControllerDispatcher */
    protected $dispatcher;

    /** @var Container */
    protected $container;

    /** @var string[] */
    protected $controllers = [
        'v1'   => '',
        'v1.1' => '',
    ];

    /**
     * ApiGatewayController constructor.
     *
     * @param Request              $request
     * @param ControllerDispatcher $dispatcher
     * @param Container            $container
     */
    public function __construct(
        Request $request,
        ControllerDispatcher $dispatcher,
        Container $container
    ) {
        $this->request    = $request;
        $this->dispatcher = $dispatcher;
        $this->container  = $container;
    }

    /**
     * @return mixed
     * @throws BindingResolutionException
     * @throws NotFoundApiVersionException
     */
    public function __invoke()
    {
        return $this->dispatchApiAction('__invoke');
    }

    /**
     * @inheritdoc
     * @throws BindingResolutionException
     * @throws NotFoundApiVersionException
     */
    public function __call($method, $parameters)
    {
        return $this->dispatchApiAction($method);
    }

    public function callAction($method, $parameters)
    {
        array_shift($parameters);

        $response = $this->{$method}(...array_values($parameters));

        if ($response instanceof JsonResource) {
            LoadReduce::capture($response);
        } elseif (is_array($response) && array_key_exists('data', $response)) {
            LoadReduce::capture($response);
        }

        return $response;
    }

    /**
     * @param $method
     *
     * @return mixed
     * @throws BindingResolutionException
     * @throws NotFoundApiVersionException
     */
    protected function dispatchApiAction($method)
    {
        defined('CONTROLLER_START') or define('CONTROLLER_START', microtime(true));

        // cache for 15 seconds.
        if ($this->shouldBeCached()) {
            $cacheId = md5(sprintf(
                '%s?%s',
                $this->request->getRequestUri(),
                http_build_query(array_merge(
                    $this->request->all(),
                    $this->request->route()->parameters(),
                    ['locale' => app()->getLocale(), 'ver'=> ResourceGate::getVersion()]
                )))
            );

            // echo $cacheId, exit;

            try {
                return localCacheStore()->remember(
                    $cacheId,
                    config('app.cache_anonymous_request_tll'),
                    fn () => $this->dispatcher->dispatch(
                        $this->request->route(),
                        $this->getController(),
                        $method
                    )
                );
            } catch (\Exception $error) {
                Log::error($error->getMessage());
            }
        }

        return $this->dispatcher->dispatch(
            $this->request->route(),
            $this->getController(),
            $method
        );
    }

    /**
     * @return string
     */
    private function getFallbackVersion(): string
    {
        $reqVersion        = ResourceGate::getVersion();
        $availableVersions = array_keys($this->controllers);

        rsort($availableVersions);

        foreach ($availableVersions as $version) {
            if (version_compare($reqVersion, $version, '>=')) {
                return $version;
            }
        }

        return last($availableVersions);
    }

    /**
     * @return Controller
     * @throws BindingResolutionException
     * @throws NotFoundApiVersionException
     */
    protected function getController()
    {
        $version = $this->getFallbackVersion();

        $controller = $this->controllers[$version] ?? null;

        if (!$controller) {
            throw new NotFoundApiVersionException();
        }

        return $this->container->make($controller);
    }

    protected function shouldBeCached(): bool
    {
        if (Auth::user()?->id) {
            return false;
        }

        if (config('app.debug')) {
            return false;
        }

        if ('GET' != $this->request->method()) {
            return false;
        }

        $path = $this->request->getPathInfo();

        // Ignore all download and callback URL
        if (preg_match('%api\/v[1-9\.]+(?:\/\S+)*(?:\/download|\/callback)(?:\/\S+)*%', $path)) {
            return false;
        }

        return true;
    }
}
