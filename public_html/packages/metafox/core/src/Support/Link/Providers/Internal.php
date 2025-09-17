<?php

namespace MetaFox\Core\Support\Link\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route as RouteFacade;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\SEO\Models\Meta;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use MetaFox\SEO\SeoMetaData;
use MetaFox\User\Models\User;
use MetaFox\User\Models\User as UserModel;

/**
 * @SuppressWarnings(PHPMD)
 */
class Internal extends AbstractLinkProvider
{
    private array $options = [];
    private User  $context;

    /**
     * @param User  $context
     * @param array $options
     */
    public function __construct(User $context, array $options = [])
    {
        parent::__construct($options);

        $this->context = $context;
    }

    public function verifyUrl(string $url, &$matches = []): bool
    {
        return url_utility()->isAppUrl($url);
    }

    public function parseUrl(string $url): ?array
    {
        if (!$this->verifyUrl($url, $matches)) {
            return null;
        }

        $route = $this->getRoute($url);

        if (!$route instanceof Route) {
            return null;
        }

        $meta = $this->getMeta($route->uri);

        if (!$meta instanceof Meta) {
            return null;
        }

        $resource = ResourceGate::getItem($meta->item_type, Arr::get($route->parameters(), 'id'));

        if (!$resource instanceof Model) {
            return null;
        }

        if (!$resource instanceof Entity) {
            return null;
        }

        if (!$this->checkPermissionOnResource($resource, $this->context, $this->options)) {
            return null;
        }

        return $this->buildResult($resource, $meta);
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    private function buildResult(Entity $resource, Meta $meta): ?array
    {
        $sharingMeta = resolve(MetaRepositoryInterface::class)
            ->getSeoSharingData($meta->resolution, $meta->name, $resource->entityType(), $resource->entityId());

        if (!$sharingMeta instanceof SeoMetaData) {
            return null;
        }

        $description = Arr::get($sharingMeta, 'og:description');

        if ($description) {
            app('events')->dispatch('core.parse_content', [$resource, &$description, [
                'parse_url' => false,
            ]]);
        }

        return [
            'title'       => Arr::get($sharingMeta, 'og:title'),
            'description' => $description,
            'image'       => Arr::get($sharingMeta, 'og:image'),
        ];
    }

    private function checkPermissionOnResource(Model $resource, UserModel $context, array $params): bool
    {
        $owner = $resource->owner;

        if (!$owner instanceof Model) {
            return true;
        }

        if (!$owner instanceof HasPrivacyMember) {
            return true;
        }

        $results = app('events')->dispatch('core.can_share_on_owner', [$resource, $context, $params]);

        if (!is_array($results)) {
            return false;
        }

        return in_array(true, array_values($results), true);
    }

    private function getRoute(string $url): ?Route
    {
        try {
            $request = Request::create($this->addSharingPrefix($url));

            return RouteFacade::getRoutes()->match($request);
        } catch (\Exception $exception) {
            Log::channel('dev')->error('Route not found', [$exception->getMessage()]);

            return null;
        }
    }

    private function addSharingPrefix(string $url): string
    {
        $urlComponents = parse_url($url);
        $path          = isset($urlComponents['path']) ? ('/' . ltrim($urlComponents['path'], '/')) : '';

        return sprintf(
            '%s://%s/sharing%s',
            $urlComponents['scheme'],
            $urlComponents['host'],
            $path,
        );
    }

    private function getMeta(string $url): ?Meta
    {
        $url = str_replace('sharing/', '', $url);

        return Meta::query()
            ->where('resolution', 'web')
            ->where('url', '=', $url)
            ->first();
    }
}
