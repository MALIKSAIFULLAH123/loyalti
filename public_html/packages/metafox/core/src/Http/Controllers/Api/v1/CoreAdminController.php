<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Core\Http\Controllers\Api\v1;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use  MetaFox\Platform\Http\Resources\Abstract\ResourceCollection;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\AdminSearch;
use MetaFox\Core\Repositories\AdminSearchRepositoryInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Core\Repositories\Eloquent\DriverRepository;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Http\Controllers\Api\ApiController;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Helper\Pagination;

/**
 * Class CoreAdminController.
 * @codeCoverageIgnore
 * @ignore
 * @group admin/core
 * @authenticated
 */
class CoreAdminController extends ApiController
{
    /**
     * @param Request $request
     * @param string  $formName
     *
     * @return JsonResponse
     * @link CoreController::showForm()
     */
    public function showForm(Request $request, string $formName)
    {
        if (!$formName) {
            return $this->error(__p('core::validation.could_not_find_form'));
        }

        /** @var AbstractForm $form */
        $driver = resolve(DriverRepository::class)
            ->getDriver(Constants::DRIVER_TYPE_FORM, $formName, 'admin');

        $form = resolve($driver);

        if (method_exists($form, 'boot')) {
            app()->call([$form, 'boot'], $request->route()->parameters());
        }

        return $this->success($form->toArray($request));
    }

    public function showDrivers(Request $request)
    {
        $q     = $request->get('q');
        $type  = $request->get('type');
        $limit = $request->get('limit', Pagination::DEFAULT_ITEM_PER_PAGE);

        $query = resolve(DriverRepository::class)
            ->getModel()
            ->newModelQuery()
            ->orderBy('name');

        if ($type) {
            $query->where('type', $type);
        }

        if ($package = $request->get('package_id')) {
            $query->where(['package_id' => $package]);
        }

        if ($q) {
            $query = $query->addScope(new SearchScope($q, ['name']));
        }

        return new ResourceCollection($query->paginate($limit));
    }

    /**
     * Search box.
     *
     * query parameters
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q');
        $locale = $request->get('locale', app()->getLocale());

        // Manually validate the locale submitted from client
        if (!in_array($locale, Language::getAllLocales())) {
            $locale = 'en';
        }

        // DO NOT REMOVE LEADING WHITESPACE!.
        $pattern = sprintf('%% %s%%', $search);

        $adminSearchRepository = resolve(AdminSearchRepositoryInterface::class);

        /** @var Collection<AdminSearch> $query */
        $query = $adminSearchRepository
            ->getModel()
            ->newQuery()
            ->where('lang', $locale)
            ->where('text', $adminSearchRepository->likeOperator(), $pattern)
            ->whereIn('package_id', resolve('core.packages')->getActivePackageIds())
            ->limit(10)
            ->get(['id', 'title', 'url', 'caption', 'group']);

        $data = $query
            ->map(function (AdminSearch $item) {
                return [
                    'value'   => 'id',
                    'label'   => $item->title,
                    'url'     => $item->url,
                    'group'   => $item->group,
                    'caption' => $item->caption,
                ];
            })
            ->toArray();

        return $this->success(array_values($data));
    }

    /**
     * View system information.
     * @return JsonResponse
     * @group admin/maintain
     */
    public function getSystemOverview(): JsonResponse
    {
        $phpItems = [];
        /** @var false|string[] $loadAvg */
        $loadAvg = sys_getloadavg();

        if ($loadAvg) {
            $phpItems[] = ['label' => 'Load Avg (1 min)', 'value' => $loadAvg[0] ?? 'N/A'];
            $phpItems[] = ['label' => 'Load Avg (5 min)', 'value' => $loadAvg[1] ?? 'N/A'];
            $phpItems[] = ['label' => 'Load Avg (15 min)', 'value' => $loadAvg[2] ?? 'N/A'];
            $phpItems[] = ['label' => 'Memory Usages', 'value' => round(memory_get_usage() / 1024 / 1024) . 'M'];
            $phpItems[] = [
                'label' => 'Memory Peak Usages', 'value' => round(memory_get_peak_usage() / 1024 / 1024) . 'M',
            ];
            $phpItems[] = ['label' => 'Memory Limit', 'value' => ini_get('memory_limit')];
        }

        $phpItems[] = ['label' => 'MetaFox Version', 'value' => MetaFoxConstant::VERSION];
        $phpItems[] = ['label' => 'PHP Version', 'value' => phpversion()];
        $phpItems[] = ['label' => 'Zend engine version', 'value' => zend_version()];
        $phpItems[] = ['label' => 'Laravel Version', 'value' => app()->version()];
        $phpItems[] = ['label' => 'Operating System', 'value' => php_uname()];
        $phpItems[] = ['label' => 'PHP Sapi', 'value' => php_sapi_name()];
        $phpItems[] = ['label' => 'PHP Safe Mode', 'value' => ini_get('safe_mode')];
        $phpItems[] = ['label' => 'PHP Disabled Functions', 'value' => ini_get('disable_functions')];
        $phpItems[] = ['label' => 'PHP Disabled Classes', 'value' => ini_get('disable_classes')];

        $phpItems[] = ['label' => 'Available Server Memory', 'value' => php_sapi_name()];
        $phpItems[] = ['label' => 'PHP Open Basedir', 'value' => ini_get('open_basedir')];
        $phpItems[] = ['label' => 'max_execution_time', 'value' => ini_get('max_execution_time')];
        $phpItems[] = ['label' => 'hard_timeout', 'value' => ini_get('hard_timeout')];

        $phpItems[] = ['label' => 'sys_get_temp_dir', 'value' => sys_get_temp_dir()];
        $phpItems[] = ['label' => 'php_ini_loaded_file', 'value' => php_ini_loaded_file()];
        $phpItems[] = ['label' => 'php_ini_scanned_files', 'value' => php_ini_scanned_files()];
        $phpItems[] = ['label' => 'Loaded Extensions', 'value' => implode(', ', get_loaded_extensions(false))];

        $serverKeys = [
            'USER', 'HOME', 'DOCUMENT_ROOT', 'DOCUMENT_URI', 'SERVER_NAME',
            'SERVER_PORT', 'SERVER_ADDR',
            'SERVER_PROTOCOL', 'SERVER_SIGNATURE', 'SERVER_ADMIN',
            'SERVER_SOFTWARE', 'GATEWAY_INTERFACE', 'PATH_TRANSLATED',
            'SCRIPT_NAME', 'SCRIPT_FILENAME', 'SCRIPT_URI',
        ];

        foreach ($serverKeys as $key) {
            $phpItems[] = ['label' => $key, 'value' => $_SERVER[$key] ?? ''];
        }

        return $this->success(['title' => 'System Overview', 'items' => $phpItems]);
    }

    /**
     * Get phpinfo.
     *
     * @return JsonResponse
     */
    public function getPhpInfo(): JsonResponse
    {
        $sections = [];
        ob_start();
        phpinfo();
        $s = ob_get_contents();
        ob_end_clean();

        $s     = strip_tags($s, '<h2><th><td>');
        $s     = preg_replace('/<th[^>]*>([^<]+)<\/th>/', '<info>\1</info>', $s);
        $s     = preg_replace('/<td[^>]*>([^<]+)<\/td>/', '<info>\1</info>', $s);
        $t     = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
        $count = count($t);
        $p1    = '<info>([^<]+)<\/info>';
        $p2    = '/' . $p1 . '\s*' . $p1 . '\s*' . $p1 . '/';
        $p3    = '/' . $p1 . '\s*' . $p1 . '/';
        for ($i = 1; $i < $count; $i++) {
            if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $t[$i], $matchs)) {
                $name    = trim($matchs[1]);
                $section = ['title' => $name, 'items' => []];
                $vals    = explode("\n", $t[$i + 1]);
                foreach ($vals as $val) {
                    if (preg_match($p2, $val, $matchs)) { // 3cols
                        $section['items'][] = ['label' => trim($matchs[1]), 'value' => trim($matchs[2])];
                    //                        $items[$name][trim($matchs[1])] = array(trim($matchs[2]), trim($matchs[3]));
                    } elseif (preg_match($p3, $val, $matchs)) { // 2cols
                        $section['items'][] = ['label' => trim($matchs[1]), 'value' => trim($matchs[2])];
                    }
                }
                $sections[] = $section;
            }
        }

        return $this->success(['title' => 'phpinfo()', 'sections' => $sections]);
    }

    /**
     * Get routes info.
     *
     * @return JsonResponse
     */
    public function getRouteInfo(): JsonResponse
    {
        Artisan::call('route:list', ['--sort' => 'uri', '--json' => true]);
        $output = json_decode(Artisan::output(), true);

        foreach ($output as $index => $row) {
            $output[$index]['uri'] = str_replace('{ver}', 'v1', $row['uri']);
            $output[$index]['id']  = $index + 1;
        }

        return $this->success($output);
    }

    /**
     * Get all registered events.
     *
     * @return JsonResponse
     */
    public function getEventInfo(): JsonResponse
    {
        $events = [];

        foreach (app()->getProviders(EventServiceProvider::class) as $provider) {
            $providerEvents = array_merge_recursive(
                $provider->shouldDiscoverEvents() ? $provider->discoverEvents() : [],
                $provider->listens()
            );

            $events = array_merge_recursive($events, $providerEvents);
        }

        $data = collect($events)->map(function ($listeners, $event) {
            return array_map(function ($listener) use ($event) {
                return ['Event' => $event, 'Listener' => $listener];
            }, $listeners);
        })->flatten(1)->sortBy('Event')->values()->toArray();

        // pretty prints
        foreach ($data as $index => $row) {
            $data[$index]['id'] = $index + 1;
        }

        return $this->success($data);
    }

    /**
     * Show data grid.
     *
     * @queryParam dataGrid string required Grid name. Example: phrase_admin
     *
     * @param Request $request
     * @param string  $gridName
     * @param mixed   $parentId
     *
     * @return JsonResponse
     */
    public function showDataGrid(Request $request, string $gridName, $parentId = null): JsonResponse
    {
        $driver = resolve(DriverRepositoryInterface::class)
            ->getDriver(Constants::DRIVER_TYPE_DATA_GRID, \Str::snake($gridName), 'admin');

        if (!$driver) {
            throw new \InvalidArgumentException(__p('validation.invalid'));
        }

        $grid = new $driver($gridName);

        if (method_exists($grid, 'boot')) {
            app()->call([$grid, 'boot'], array_merge(['parentId' => $parentId], $request->route()->parameters()));
        }

        return $this->success($grid);
    }
}
