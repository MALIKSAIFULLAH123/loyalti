<?php

namespace MetaFox\SEO\Repositories\Eloquent;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use MetaFox\Platform\Contracts\HasAvatarMorph;
use MetaFox\Platform\Contracts\HasCoverMorph;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasUserProfile;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\SEO\Models\Meta;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;
use MetaFox\SEO\Repositories\SchemaRepositoryInterface;
use MetaFox\SEO\SeoMetaData;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\User as UserFacade;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class MetaRepository.
 * @method Meta find($id, $columns = ['*'])
 */
class MetaRepository extends AbstractRepository implements MetaRepositoryInterface
{
    public function model()
    {
        return Meta::class;
    }

    protected function schemaRepository(): SchemaRepositoryInterface
    {
        return resolve(SchemaRepositoryInterface::class);
    }

    public function fillPlaceholders($input, $resource)
    {
        if (!is_string($input)) {
            return $input;
        }

        if (!preg_match_all('/\{\w+\}/m', $input, $matches, PREG_PATTERN_ORDER, 0)) {
            return $input;
        }

        $replacements = [];

        foreach ($matches as $match) {
            try {
                $value = $resource->{trim($match[0], '{}')};
            } catch (\Exception $exception) {
                // silent pass.
            }
            $replacements[$match[0]] = $value ?? '';
        }

        return strtr($input, $replacements);
    }

    /**
     * @throws AuthenticationException
     */
    public function getSeoSharingData(
        string   $resolution,
        string   $nameOrUrl,
        mixed    $type = null,
        mixed    $id = null,
        \Closure $callback = null
    )
    {
        $resource = null;
        /** @var ?Meta $meta */
        $meta    = null;
        $context = Auth::user() ?? UserFacade::getGuestUser();
        $robots  = 'index, follow';

        try {
            $meta = Meta::query()
                ->where('resolution', $resolution)
                ->where(fn ($query) => $query->where('name', '=', $nameOrUrl)->orWhere('url', '=', $nameOrUrl))
                ->first();

            if ($type && $id) {
                $resource = \MetaFox\Platform\Facades\ResourceGate::getItem($type, $id);
            }
        } catch (\Exception $exception) {
            // ignore exception
        }

        // todo:  check site private mode, prevent guest to view content.

        $siteName  = Settings::get('core.general.site_name');
        $siteTitle = Settings::get('core.general.site_title');
        $delimiter = Settings::get('core.general.title_delim');
        $title     = $resource?->title;

        if (!$title) {
            $title = $meta?->title;
        }

        if ($resource && !$title && method_exists($resource, 'toTitle')) {
            $title = $resource->toTitle();
        }

        /*
         * This is best prioritized layer that allow apps to control their title
         */
        if (is_string($resource?->seo_title) && trim($resource?->seo_title) !== MetaFoxConstant::EMPTY_STRING) {
            $title = trim($resource->seo_title);
        }

        /**
         * check privacy support does not allow user preview content.
         * user guest does not allow user preview content.
         */
        $canViewResource = $this->canViewResource($context, $resource);
        if (!$canViewResource) {
            $title = __p('core::phrase.content_is_not_available');
        }

        $title    = parse_output()->limit($title, MetaFoxConstant::DEFAULT_MAX_SEO_TITLE_LENGTH);
        $ogTitle  = rtrim(trim($title ? $title : $siteTitle), '.');
        $title    = $title ? $title . " {$delimiter} " . $siteName : $siteTitle;
        $image    = null;
        $keywords = $resource?->keywords ?? $meta?->keywords;
        $images   = $canViewResource ? $resource?->images : null;
        $profile  = $resource instanceof HasUserProfile ? $resource->profile : $resource;

        if ($canViewResource && $profile instanceof HasCoverMorph) {
            $images = $profile->covers;
        }

        if ($canViewResource && $profile instanceof HasAvatarMorph) {
            $images = $profile->avatars;
        }

        // Supporting a way for a resource to provide its custom og:images
        $customImages = [];
        if ($canViewResource && is_object($resource) && method_exists($resource, 'toOGImages')) {
            $customImages = $resource->toOGImages();
        }

        if (!empty($customImages)) {
            $images = $customImages;
        }

        // $preferSizes = ['500', '240', '200', '1024','150', '200x200'];
        $preferSizes = ['1024', 'origin'];

        if (is_array($images)) {
            foreach ($preferSizes as $size) {
                if (isset($images[$size])) {
                    $image = $images[$size];
                    break;
                }
            }
        }

        if (!$image) {
            $defaultLogo = resolve(AssetRepositoryInterface::class)
                ->getModel()
                ->newModelQuery()
                ->where('name', 'image_welcome')
                ->where('package_id', 'metafox/layout')
                ->first();

            $image = $defaultLogo?->url ?? $resource?->image;
        }

        $description = null;
        if ($canViewResource && is_object($resource) && method_exists($resource, 'toOGDescription')) {
            $description = $resource?->toOGDescription($context);
            $description = $description ? strip_tags(substr($description ?? '', 0, 225)) : null;
        }

        if (!$description) {
            $description = $meta?->description;
        }

        $url        = null;
        $ogImageAlt = $image;

        if ($canViewResource && is_object($resource) && method_exists($resource, 'toUrl')) {
            $url = $resource?->toUrl();
        }

        if (null === $url) {
            $url = config('app.url');
        }

        $canonicalUrl = $url;

        if (is_object($resource) && method_exists($resource, 'toCanonicalUrl')) {
            $canonicalUrl = $resource?->toCanonicalUrl();
        }

        if ($meta?->canonical_url) {
            $canonicalUrl = $meta->canonical_url;
        }

        if (!$description) {
            $description = Settings::get('core.general.description');
        }

        if (!$keywords) {
            $keywords = Settings::get('core.general.keywords');
        }

        if ($canViewResource && is_object($resource) && method_exists($resource, 'buildSeoData')) {
            $extra = $resource->buildSeoData($resolution, $nameOrUrl, $type, $id);
        }

        $privateItem = $resource instanceof HasPrivacy && $resource->privacy !== MetaFoxPrivacy::EVERYONE;

        if ($meta?->robots_no_index || $privateItem) {
            $robots = 'noindex, nofollow';
        }

        $schemaStructure = [];

        if (!$privateItem) {
            try {
                $schemaStructure = $meta ? $this->schemaRepository()->buildSEOMetaSchemas($meta, $resource) : [];
            } catch (\Exception $exception) {
            }
        }

        /*
         * List of open graph support.
         * @link https://developers.facebook.com/docs/sharing/webmasters/
         */
        $sharingMeta = new SeoMetaData(array_merge([
            'title'           => $title,
            'keywords'        => $keywords,
            'description'     => htmlspecialchars($description),
            'fb:app_id'       => Settings::get('core.services.facebook.app_id'),
            'og:locale'       => app()->getLocale(),
            'og:type'         => 'website',
            'og:image'        => $image,
            'og:image:width'  => 600,
            'og:image:height' => 315,
            'og:title'        => $ogTitle,
            'og:updated_time' => $resource?->updated_at,
            'og:url'          => $url,
            'canonical'       => $canonicalUrl,
            'og:image:alt'    => $ogImageAlt,
            'og:site_name'    => $siteName,
            'og:description'  => htmlspecialchars($description),
            'og:video'        => null,
            'twitter:card'    => 'summary',
            'twitter:image'   => $image,
            'robots'          => $robots,
            // @link \MetaFox\SEO\Http\Controllers\Api\v1\MetaAdminController::translate
            'meta:name'       => $meta?->name,
            'schema'          => $schemaStructure,
            'breadcrumbs'     => [],
        ], $extra ?? []));

        if ($meta?->resolution === 'admin') {
            $package = resolve('core.packages')->findByName($meta?->package_id);

            $sharingMeta->addBreadcrumb(__p('core::phrase.dashboard'), '/');
            $sharingMeta->addBreadcrumb($package?->title, $package?->internal_admin_url);

            if (!$meta?->custom_sharing_route) {
                $label = $resource->title ?? $meta?->heading ?? $meta->title;
                $sharingMeta->addBreadcrumb($label);
            }
        }

        if ($callback) {
            $callback($sharingMeta, $resource);
        }

        foreach ($sharingMeta as $name => $value) {
            $sharingMeta[$name] = $this->fillPlaceholders($value, $resource);
        }

        return $sharingMeta;
    }

    protected function canViewResource(User $context, ?Model $resource): bool
    {
        if (!$resource instanceof HasPrivacy) {
            return true;
        }

        if ($context->can('view', [$resource])) {
            return true;
        }

        return false;
    }

    public function getSeoSharingView(
        string   $resolution,
        string   $nameOrUrl,
        mixed    $type = null,
        mixed    $id = null,
        \Closure $callback = null
    )
    {
        $data = $this->getSeoSharingData($resolution, $nameOrUrl, $type, $id, $callback);

        if (defined('MFOX_SHARING_RETRY_ARRAY')) {
            return ['data' => $data];
        }

        $htmlHeaders = [];

        foreach (['keywords', 'description', 'twitter:card', 'twitter:image', 'robots'] as $nameOrUrl) {
            if (!$data[$nameOrUrl]) {
                continue;
            }
            $htmlHeaders[] = sprintf('    <meta name="%s" content="%s" />', $nameOrUrl, $data[$nameOrUrl]);
        }

        $canonicalLink = Arr::get($data, 'canonical');
        if ($canonicalLink) {
            $htmlHeaders[] = sprintf('    <link rel="canonical" href="%s" />', $canonicalLink);
        }

        foreach ($data as $nameOrUrl => $value) {
            if (!$value) {
                continue;
            }
            if (!str_starts_with($nameOrUrl, 'og:') && !str_starts_with($nameOrUrl, 'fb:')) {
                continue;
            }
            $htmlHeaders[] = sprintf('    <meta property="%s" content="%s" />', $nameOrUrl, $data[$nameOrUrl]);
        }

        /* append head html */
        $htmlHeaders[] = Settings::get('core.end_head_html', '');

        return view('opengraph.sharing', [
            'data'        => $data,
            'header_html' => implode(PHP_EOL, $htmlHeaders),
        ]);
    }

    public function getByName(string $name): ?Meta
    {
        $name = normalize_seo_meta_name($name);

        return $this->where([['name', '=', $name]])->limit(1)->first();
    }

    public function setupSEOMetas(string $package, array $pages): void
    {
        if (empty($pages)) {
            return;
        }
        $shouldDeleteItems = [];
        $moduleId          = PackageManager::getAlias($package);
        $fields            = (new Meta())->getFillable();

        $inserts = [];
        foreach ($pages as $row) {
            if ($row['deleted'] ?? false) {
                $shouldDeleteItems[] = $row['name'];
                continue;
            }

            $inserts[] = Arr::only(array_merge([
                'package_id'           => $package,
                'module_id'            => $moduleId,
                'name'                 => '',
                'item_type'            => null,
                'page_type'            => null,
                'phrase_heading'       => null,
                'phrase_title'         => null,
                'phrase_keywords'      => null,
                'phrase_description'   => null,
                'menu'                 => null,
                'custom_sharing_route' => 0,
                'secondary_menu'       => null,
                'resolution'           => str_starts_with($row['name'], 'admin.') ? 'admin' : 'web',
                'url'                  => null,
                'resource_name'        => null,
            ], $row), $fields);
        }

        Meta::query()->upsert($inserts, ['name']);

        if (count($shouldDeleteItems)) {
            Meta::query()->whereIn('name', $shouldDeleteItems)->delete();
        }
    }

    /**
     * @param string $package
     * @return array
     */
    public function dumpSEOMetas(string $package): array
    {
        $rows = $this->getModel()->newQuery()
            ->where([
                'package_id' => $package,
            ])
            ->orderBy('name')
            ->get([
                'name',
                'phrase_title',
                'phrase_description',
                'phrase_keywords',
                'phrase_heading',
                'menu',
                'url',
                'item_type',
                'custom_sharing_route',
                'page_type',
                'secondary_menu',
                'resource_name',
            ])->toArray();

        return array_map(function (array $values) {
            return array_trim_null($values, [
                'is_verified'          => 1,
                'url'                  => '',
                'secondary_menu'       => '',
                'menu'                 => '',
                'custom_sharing_route' => 0,
                'item_type'            => null,
                'resource_name'        => null,
                'page_type'            => null,
            ]);
        }, array_values($rows));
    }

    public function createSampleMeta(string $name, string $url = null): Meta
    {
        $name = normalize_seo_meta_name($name);

        /** @var Meta $model */
        $model = $this->getModel()->newInstance();
        [$alias] = explode('.', Str::replace('admin.', '', $name), 3);
        $admin      = str_starts_with($name, 'admin.');
        $resolution = $admin ? 'admin' : 'web';
        $prefix     = normalize_seo_meta_phrase($name);

        $title = $prefix;

        $heading     = $admin ? $title : $prefix . '_heading';
        $keywords    = $admin ? null : $prefix . '_keywords';
        $description = $admin ? null : $prefix . '_desription';

        $data = [
            'name'               => $name,
            'package_id'         => PackageManager::getByAlias($alias) ?? 'metafox/core',
            'url'                => $url,
            'phrase_title'       => $title,
            'phrase_heading'     => $heading,
            'phrase_keywords'    => $keywords,
            'phrase_description' => $description,
            'resolution'         => $resolution,
        ];
        $model->fill($data);
        $model->save();

        $phrases = [$title => $title];

        if ($keywords) {
            $phrases[$keywords] = '';
        }
        if ($heading) {
            $phrases[$heading] = '';
        }
        if ($description) {
            $phrases[$description] = '';
        }

        app('phrases')->updatePhrases($phrases);

        $model->refresh();

        return $model;
    }
}
