<?php

namespace MetaFox\Core\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\App\Models\Package;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Authorization\Repositories\Contracts\PermissionRepositoryInterface;
use MetaFox\Core\Models\AdminSearch;
use MetaFox\Core\Models\Driver;
use MetaFox\Core\Repositories\AdminSearchRepositoryInterface;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Menu\Models\MenuItem;
use MetaFox\Menu\Repositories\MenuItemRepositoryInterface;
use MetaFox\Platform\PackageManager;
use MetaFox\Platform\Repositories\AbstractRepository;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class AdminSearchRepository.
 */
class AdminSearchRepository extends AbstractRepository implements AdminSearchRepositoryInterface
{
    /**
     * @var array <string,string>
     */
    protected array $stopWords = [];

    /**
     * @var array <string,string>
     */
    protected array $captions = [];

    public const DELIM = '»';

    public function model()
    {
        return AdminSearch::class;
    }

    /**
     * @throws \Exception
     */
    public function scanApp(Package $app): void
    {
        $caption = sprintf('Apps %s %s', self::DELIM, $app->title);

        $this->fromMenuItems([
            ['menu', '=', sprintf('%s.admin', $app->alias)],
            ['is_active', '=', 1],
        ], $caption, $app->title);

        $this->scanAppDrivers($app, 'form-settings', $app->title);
        $this->scanAppDrivers($app, 'form-mailer', $app->title);
        $this->scanAppDrivers($app, 'form-cache', $app->title);
        $this->scanAppDrivers($app, 'form-logger', $app->title);
        $this->scanAppDrivers($app, 'form-storage', $app->title);
        $this->scanAppDrivers($app, 'form-queue', $app->title);
        $this->scanAppPermissions($app, sprintf('%s %s Permissions', $app->title, self::DELIM), $app->title);
    }

    private function initStopWords(): void
    {
        if (!empty($this->stopWords)) {
            return;
        }

        $phrases = [
            'core::phrase.save',
            'core::phrase.save_changes',
            'core::phrase.search',
            'core::phrase.search_dot',
            'core::phrase.update',
            'core::phrase.cancel',
            'core::phrase.continue',
        ];

        foreach ($phrases as $phrase) {
            $this->stopWords[__p($phrase)] = 'yes';
        }
    }

    public function getCaption(string $key)
    {
        if (!isset($this->captions[$key])) {
            $module               = resolve('core.packages')->getPackageByName($key);
            $this->captions[$key] = $module ? $module->title : '';
        }

        return $this->captions[$key];
    }

    public function normalize(array $data): array|false
    {
        extract($data);

        if (!@$url || !@$title || !@$package_id) {
            return false;
        }

        if (isset($this->stopWords[$title])) {
            return false;
        }

        if (!isset($caption)) {
            $caption = $this->getCaption($package_id);
        }

        $title = strip_tags($title);

        if (!@$text) {
            $text = $title;
        }

        if (!isset($group)) {
            $group = 'Apps';
        }

        $title   = $this->trimTitle($title);
        $group   = $group ?? $caption;
        $caption = $this->trimCaption($caption, $title);

        return [
            'title'      => $title,
            'url'        => $url,
            'uid'        => md5(strtolower(sprintf('%s.%s.%s.%s', $title, $caption, $url, app()->getLocale()))),
            'group'      => $group,
            'caption'    => $caption,
            'text'       => $this->trimText($text, $title, $group, $caption),
            'package_id' => $package_id,
            'module_id'  => $module_id ?? PackageManager::getAlias($package_id),
        ];
    }

    public function upsert(array $data): void
    {
        $this->initStopWords();

        $data = array_filter(array_map([$this, 'normalize'], $data), function ($x) {
            return (bool) $x;
        });

        if (!count($data)) {
            return;
        }

        $keys = [];
        $ret  = [];
        foreach ($data as $elem) {
            $arrayKey = $elem['uid'];
            if (in_array($arrayKey, $keys)) {
                continue;
            }

            $elem['lang'] = app()->getLocale();

            $ret[] = $elem;
            array_push($keys, $arrayKey);
        }
        // Cardinality violation: 7 ERROR:  ON CONFLICT DO UPDATE command cannot affect row a second time
        $this->getModel()->newQuery()->upsert($ret, ['uid'], ['group', 'package_id', 'module_id']);
    }

    private function trimText($caption, $group, $title, $text): string
    {
        $data = array_map(function ($val) {
            return trim(preg_replace('/\s*(\*|»)/m', '', $val));
        }, [$title, $caption, $text, $group]);

        // optimize the search index
        $dataLength = count($data);
        usort($data, fn ($a, $b) => strlen($a) > strlen($b));
        for ($i = 0; $i < $dataLength; $i++) {
            $needle = $data[$i];
            for ($t = $i + 1; $t < $dataLength; $t++) {
                $haystack = $data[$t];
                if (strpos($haystack, $needle) !== false) {
                    // if the current text is already included in the longer text,
                    // it should be removed to minimize the search index.
                    unset($data[$i]);
                    continue;
                }
            }
        }

        // add leading space to help search correctly.
        return sprintf(' %s', implode(' ', $data));
    }

    public function clean(): void
    {
        $this->getModel()->newQuery()->truncate();
    }

    /**
     * @param  array  $wheres
     * @param  string $caption
     * @param  string $group
     * @return void
     */
    public function fromMenuItems(array $wheres, string $caption, string $group): void
    {
        /** @var Collection<MenuItem> $query */
        $query = resolve(MenuItemRepositoryInterface::class)
            ->getModel()
            ->newQuery()
            ->where($wheres)
            ->orderBy('parent_name', 'desc')
            ->cursor();

        $data = [];

        foreach ($query as $item) {
            if (!$item->package_id || !$item->label) {
                continue;
            }
            $data[] = [
                'title'      => __p($item->label),
                'caption'    => $caption,
                'group'      => $group,
                'module_id'  => $item->module_id,
                'package_id' => $item->package_id,
                'url'        => $item->to,
            ];
        }

        $this->upsert($data);
    }

    /**
     * @param  Package    $app
     * @param  string     $type
     * @param  string     $caption
     * @return void
     * @throws \Exception
     */
    private function scanAppDrivers(Package $app, string $type, string $caption)
    {
        /** @var Collection<Driver> getModel */
        $drivers = resolve(DriverRepositoryInterface::class)
            ->getModel()->newQuery()
            ->where([
                ['type', '=', $type],
                ['package_id', '=', $app->name],
                ['resolution', '=', 'admin'],
                ['is_active', '=', 1],
            ])
            ->whereNotNull('url')
            ->orderBy('name')
            ->get();

        foreach ($drivers as $driver) {
            if (!$driver->url || !$driver->driver) {
                continue;
            }

            $this->fromFormDriver($driver, $caption, $app->title);
        }
    }

    public function fromFormDriver(Driver $driver, string $caption, string $group)
    {
        $data = [];

        try {
            $formClass = $driver->driver;
            if (!$formClass || !class_exists($formClass)) {
                return;
            }

            $form = resolve($formClass);
            if (!$form instanceof AbstractForm) {
                return;
            }

            $fields = Arr::flatten($form->describe(), 1);

            $data = $this->fromFromFields($driver, $fields, $caption, $group);

            $this->upsert($data);
        } catch (\Exception $exception) {
            // skip installation process
        }
    }

    /**
     * @param Driver $driver
     * @param array  $fields
     * @param string $caption
     * @param string $group
     *
     * @return array<mixed>
     */
    private function fromFromFields(Driver $driver, array $fields, string $caption, string $group): array
    {
        $data = [];
        if (empty($fields)) {
            return $data;
        }

        if ($driver->title) {
            $caption = sprintf('%s %s %s', $caption, self::DELIM, __p($driver->title));
        }

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }

            if (!Arr::has($field, 'id')) {
                // support nested data from container (section);
                array_push($data, ...$this->fromFromFields($driver, $field, $caption, $group));
                continue;
            }

            $base = [
                'caption'    => $caption,
                'group'      => $group,
                'package_id' => $driver->package_id,
                'url'        => sprintf('%s#%s', $driver->url, $field['id']),
            ];

            if (isset($field['label']) && $field['label']) {
                $data[] = Arr::add($base, 'title', $field['label']);
            }

            if (isset($field['description']) && $field['description']) {
                $data[] = Arr::add($base, 'title', $field['description']);
            }
        }

        return $data;
    }

    private function trimTitle(string $title): string
    {
        $title = strip_tags($title);
        $title = preg_replace('/(\n|»)/um', ' ', $title);
        $title = preg_replace('/https?:\/\/([^\/]+)(\S+)/mu', '$1 ', $title);
        $title = preg_replace('/($)/m', ' ', $title);

        return mb_substr($title, 0, 200);
    }

    private function trimCaption(mixed $caption, string $title): string
    {
        return $caption;
    }

    private function scanAppPermissions(Package $app, string $caption, string $group)
    {
        /** @var Collection<Permission> $query */
        $query = resolve(PermissionRepositoryInterface::class)
            ->getModel()
            ->newQuery()
            ->where([['module_id', '=', $app->alias]])
            ->where('is_editable', 1)
            ->where('is_public', 1)
            ->whereNot('entity_type', '*')
            ->get();

        foreach ($query as $permission) {
            $url = sprintf('/%s/permission', $app->alias);
            $this->fromAppPermission($permission, $url, $app->name, $caption, $group);
        }
    }

    /**
     * @param  Permission $permission
     * @param  string     $url
     * @param  string     $package_id
     * @param  string     $caption
     * @param  string     $group
     * @return void
     */
    public function fromAppPermission(
        Permission $permission,
        string $url,
        string $package_id,
        string $caption,
        string $group
    ): void {
        $data = [];
        foreach ([
            __p($permission->getLabelPhrase()),
            __p($permission->getHelpPhrase()),
        ] as $title) {
            if (!$title || !is_string($title)) {
                continue;
            }
            $data[] = [
                'title'      => $title,
                'caption'    => $caption,
                'group'      => $group,
                'module_id'  => $permission->module_id,
                'package_id' => $package_id,
                'url'        => $url . '#' . $permission->name,
            ];
        }
        $this->upsert($data);
    }
}
