<?php

namespace MetaFox\SEO\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\PackageManager;
use MetaFox\SEO\Support\Scopes\Sitemap\ViewScope;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\User as UserFacade;
use MetaFox\User\Support\Facades\UserPrivacy;

class SitemapController extends Controller
{
    public const PER_PAGE = 500;

    /**
     * @return Response
     */
    public function index(): Response
    {
        // add form settings to manage site map exclude types.
        $excludeTypes = Settings::get('seo.sitemap_exclude_types', []);
        $items        = new \ArrayObject();

        // scan types for models.
        $data  = PackageManager::discoverSettings('getSitemap');
        $types = Arr::collapse(array_values($data));

        foreach ($types as $type) {
            // skip by seo settings.
            if (in_array($type, $excludeTypes)) {
                continue;
            }

            $modelClass = Relation::getMorphedModel($type);
            if (!$modelClass || !class_exists($modelClass)) {
                continue;
            }

            $modelInstance = resolve($modelClass);
            $query         = $modelClass::query()->addScope(new ViewScope());

            $tableName = $modelInstance->getTable();
            if (Schema::hasColumn($tableName, 'is_active')) {
                $query->where('is_active', 1);
            }

            $total = $query->count();

            $limit = max(1, ceil($total / static::PER_PAGE)); // 500 item per site map url.
            /** @var Model $modelInstance */
            $lastMod       = null;

            if (!method_exists($modelInstance, 'toUrl')) {
                continue;
            }

            if ($total == 0) {
                continue;
            }

            if (in_array('updated_at', $modelInstance->getFillable())) {
                $lastMod = DB::table($modelInstance->getTable())->max('updated_at');

                if ($lastMod) {
                    $lastMod = Carbon::create($lastMod)->tz('UTC')->toAtomString();
                }
            }

            for ($page = 0; $page < $limit; $page++) {
                $items[] = [
                    'url' => $page > 0 ?
                        sprintf('%s/sitemap/%s-%s.xml', config('app.url'), $type, $page) :
                        sprintf('%s/sitemap/%s.xml', config('app.url'), $type),
                    'lastmod' => $lastMod,
                ];
            }
        }

        $query = MenuItem::query()
        ->where('resolution', 'web')
        ->where('is_active', 1)
        ->where(function (Builder $builder) {
            $builder
                ->whereNotNull('to')
                ->orwhereNot('to', 'like', '/admincp%')
                ->orWhereNot('to', '')
                ->orWhereNot('to', '/');
        });

        $lastUpdate = $query->max('updated_at');
        $menuCount  = $query->count();

        if ($menuCount > 0) {
            $limit = max(1, ceil($total / static::PER_PAGE)); // 500 item per site map url.

            for ($page = 0; $page < $limit; $page++) {
                $items[] = [
                    'url' => $page > 0 ?
                        sprintf('%s/sitemap/pages-%s.xml', config('app.url'), $page) :
                        sprintf('%s/sitemap/pages.xml', config('app.url')),
                    'lastmod' => $lastUpdate ? Carbon::create($lastUpdate)->tz('UTC')->toAtomString() : null,
                ];
            }
        }

        // allow add others hooks.
        app('events')->dispatch('seo.sitemap_index', $items);
        $html = view('seo::sitemap.index', ['items' => $items])->render();

        return response($html)->withHeaders(['Content-Type' => 'text/xml']);
    }

    /**
     * @param  string   $type
     * @param  int|null $page
     * @return Response
     */
    public function urls(string $type, ?int $page = 0): Response
    {
        $context       = UserFacade::getGuestUser();
        $headers       = ['Content-Type' => 'text/xml'];
        $emptyResponse = view('seo::sitemap.urls', ['items' => []])->render();
        $items         = new \ArrayObject();
        $modelClass    = Relation::getMorphedModel($type);
        if (!$modelClass || !class_exists($modelClass)) {
            return response($emptyResponse)->withHeaders($headers);
        }

        /** @var Model $modelInstance */
        $modelInstance = resolve($modelClass);

        if (!$modelInstance instanceof Model) {
            return response($emptyResponse)->withHeaders($headers);
        }

        $query = $modelInstance
            ->newQuery()
            ->addScope(new ViewScope())
            ->forPage(++$page, static::PER_PAGE);

        $tableName = $modelInstance->getTable();
        if (Schema::hasColumn($tableName, 'is_active')) {
            $query->where('is_active', 1);
        }

        foreach ($query->cursor() as $row) {
            $hasNoViewProfilePermission = $row instanceof User && !UserPrivacy::hasAccess($context, $row, 'profile.view_profile');
            $hasNoViewPermission        = $row instanceof Content && !$context->can('view', [$row, $row]);

            if ($hasNoViewProfilePermission || $hasNoViewPermission) {
                continue;
            }

            if (!method_exists($row, 'toUrl')) {
                continue;
            }

            $url        = $row?->toUrl();

            if (method_exists($row, 'toSitemapUrl')) {
                $url = $row->toSitemapUrl();
            }

            if (!$url) {
                continue;
            }

            $noIndexedUrls = array_keys(Settings::get('seo.sitemap_no_indexes_urls', []));

            $isExcluded = false;
            foreach ($noIndexedUrls as $urlPattern) {
                if (is_seo_url_match($urlPattern, $url)) {
                    $isExcluded = true;
                    break;
                }
            }

            if ($isExcluded) {
                continue;
            }

            $lastMod = $row->updated_at;

            $items[] = [
                'url'     => $url,
                'lastmod' => $lastMod ? Carbon::create($lastMod)->tz('UTC')->toAtomString() : null,
            ];
        }

        $content = view('seo::sitemap.urls', ['items' => $items])->render();

        return response($content)->withHeaders($headers);
    }

    /**
     * @param  string   $type
     * @param  int|null $page
     * @return Response
     */
    public function clientUrls(?int $page = 0): Response
    {
        $headers       = ['Content-Type' => 'text/xml'];
        $emptyResponse = view('seo::sitemap.urls', ['items' => []])->render();
        $items         = new \ArrayObject();
        $parentMenus   = ['core.primaryMenu', 'core.dropdownMenu'];
        $ignoreURLs    = [
            '/admincp',
            '/search',
            '/:',
        ];

        $query = MenuItem::query()
            ->where('resolution', 'web')
            ->where('is_active', 1)
            ->where(function (Builder $menuWhere) use ($parentMenus) {
                $menuWhere->whereIn('menu', $parentMenus)
                    ->orWhere('menu', 'like', '%.sidebarMenu');
            })
            ->where(function (Builder $toWhere) use ($ignoreURLs) {
                foreach ($ignoreURLs as $url) {
                    $toWhere->where('to', 'not like', '%' . $url . '%');
                }
            })
            ->where('to', '<>', '')
            ->where('to', '<>', '/');

        $rows        = $query->forPage(++$page, static::PER_PAGE)->cursor();
        $cachedPaths = [];

        foreach ($rows as $row) {
            if (!$row instanceof MenuItem) {
                continue;
            }

            $path = $row?->to;

            if (!$path) {
                continue;
            }

            $parts = explode('?', $path);
            if (count($parts) > 1) {
                $path = $parts[0];
            }

            if ($cachedPaths[$path] ?? false) {
                continue;
            }

            $extra     = $row->extra ?? [];
            $isPrivate = Arr::get($extra, 'exclude_from_sitemap') ?: false;

            if ($isPrivate) {
                continue;
            }

            $cachedPaths[$path] = 1;

            $noIndexedUrls = array_keys(Settings::get('seo.sitemap_no_indexes_urls', []));

            $isExcluded = false;
            foreach ($noIndexedUrls as $urlPattern) {
                if (is_seo_url_match($urlPattern, $path)) {
                    $isExcluded = true;
                    break;
                }
            }

            if ($isExcluded) {
                continue;
            }

            $lastMod = $row->updated_at;

            $items[] = [
                'url'     => sprintf('%s/%s', config('app.url'), trim($path, '/')),
                'lastmod' => $lastMod ? Carbon::create($lastMod)->tz('UTC')->toAtomString() : null,
            ];
        }

        if (empty($items)) {
            return response($emptyResponse)->withHeaders($headers);
        }

        $content = view('seo::sitemap.urls', ['items' => $items])->render();

        return response($content)->withHeaders($headers);
    }
}
