<?php

namespace MetaFox\Activity\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use MetaFox\Activity\Contracts\TypeManager as TypeManagerContract;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Type;
use MetaFox\Platform\PackageManager;
use MetaFox\User\Models\User;

/**
 * Class TypeManager.
 */
class TypeManager implements TypeManagerContract
{
    /**
     * @var mixed
     */
    private $types;

    /**
     * @var
     */
    private $defaultTypes;

    /**
     * @var string
     */
    private const CACHE_NAME = 'activity_type_manager_cache';

    /**
     * @var string
     */
    private const DEFAULT_CACHE_NAME = 'activity_type_manager_default_cache';

    /**
     * @var int
     */
    private const CACHE_LIFETIME  = 3000;
    private const VIEW_ON_PROFILE = 'profile';
    private const VIEW_ON_HOME    = 'home';

    public function __construct()
    {
        $this->start();
    }

    protected function start(): void
    {
        if (!$this->types) {
            $this->types = Cache::rememberForever(self::CACHE_NAME, function () {
                $data = [];
                /**
                 * @var Type[] $types
                 */
                $types = Type::query()->where(['is_active' => true])->get();

                foreach ($types as $type) {
                    $data[$type->type] = $type->describe();
                }

                return $data;
            });
        }

        if (!$this->defaultTypes) {
            $this->defaultTypes = Cache::rememberForever(self::DEFAULT_CACHE_NAME, function () {
                $types = Arr::flatten(PackageManager::discoverSettings('getActivityTypes'), 1);

                $parsed = [];

                $defaultSettings = resolve(Type::class)->getSettings();

                foreach ($types as $type) {
                    $var = $type['type'];

                    $values = array_filter($type, function ($key) use ($defaultSettings) {
                        return in_array($key, $defaultSettings);
                    }, ARRAY_FILTER_USE_KEY);

                    Arr::set($parsed, $var, $values);
                }

                return $parsed;
            });
        }
    }

    public function getDefaultSettingsByType(string $type): array
    {
        if (!is_array($this->defaultTypes)) {
            return [];
        }

        return Arr::get($this->defaultTypes, $type, []);
    }

    public function isActive(string $type): bool
    {
        return isset($this->types[$type]);
    }

    public function hasFeature(string $type, string $feature): bool
    {
        if (!$this->isActive($type)) {
            return false;
        }

        if (!isset($this->types[$type][$feature])) {
            return false;
        }

        /*
         * Some settings have not implemented yet, so we will limit to edit these settings
         */
        if ($this->isDisabled($type, $feature)) {
            return false;
        }

        return $this->types[$type][$feature];
    }

    public function refresh(): void
    {
        cache()->deleteMultiple([self::DEFAULT_CACHE_NAME, self::CACHE_NAME]);
        $this->types        = null;
        $this->defaultTypes = null;
        $this->start();
    }

    /**
     * Create or update an activity type.
     * Note: this method won't purge cache. Please purge cache manually.
     *
     * @param array<string, mixed> $data
     *
     * @return Type|false
     */
    public function makeType($data)
    {
        $type = Type::query()
            ->where('type', '=', $data['type'])
            ->where('module_id', '=', $data['module_id'])
            ->first();

        if (!$type) {
            $type = new Type();
        }

        $defaultData = [
            'title'       => $data['module_id'],
            'description' => $data['module_id'],
            'is_active'   => 0,
            'is_system'   => 0,
        ];

        $data = array_merge($defaultData, $data);

        $fields = Type::query()
            ->getModel()
            ->getFillable();

        $values = Arr::except($data, $fields);
        $row    = Arr::only($data, $fields);

        $row['value_default'] = $values;

        $type->fill($row);

        return $type->save() ? $type : false;
    }

    public function getTypePhrase(string $type): ?string
    {
        if (!$this->isActive($type)) {
            return null;
        }

        $text = $this->types[$type]['description'];

        if (!is_string($text)) {
            return null;
        }

        return __p($text);
    }

    public function getTypePhraseWithContext(Feed $feed, int $profileId = 0): ?string
    {
        $type = $feed->type_id;

        if (!$this->isActive($type)) {
            return null;
        }

        $text = $this->types[$type]['description'];
        $feed->loadMissing('userEntity');

        if (!is_string($text)) {
            return null;
        }

        $params = $this->types[$type]['params'];

        if (empty($params)) {
            return __p($text);
        }

        $feedParams = [];

        $dataFlatten = Arr::dot($feed->toArray());

        Arr::set($dataFlatten, 'is_auth_user', 0);

        if ($feed->owner instanceof User && $feed->userId() == user()->entityId()) {
            Arr::set($dataFlatten, 'is_auth_user', 1);
        }

        foreach ($params as $phraseKey => $mappingObjectKey) {
            $feedParams[$phraseKey] = $dataFlatten[$mappingObjectKey] ?? null;
        }

        return __p($text, $feedParams);
    }

    public function hasSetting(string $type, string $feature): bool
    {
        if (!$this->isActive($type)) {
            return false;
        }

        if (!isset($this->types[$type])) {
            return false;
        }

        if (!isset($this->types[$type][$feature])) {
            return false;
        }

        return true;
    }

    public function getTypes(): array
    {
        return $this->types ?: [];
    }

    public function getAbilities(): array
    {
        $type = new Type();

        return $type->getAbilities();
    }

    public function getAllowValue(): array
    {
        $type = new Type();

        return $type->getAllowValue();
    }

    public function getAllowAbilities(): array
    {
        $type = new Type();

        return $type->getAllowAbilities();
    }

    public function getTypeSettings(): array
    {
        $types = $this->getTypes();

        $abilities      = array_keys($this->getAbilities());
        $allowAbilities = array_keys($this->getAllowValue());

        foreach ($types as $key => $type) {
            $only      = Arr::only($type, $abilities);
            $onlyAllow = Arr::only($type, $allowAbilities);

            $only = array_map(function ($value) {
                return (bool)$value;
            }, $only);

            $result    = array_merge($only, $this->getAllowValue(), $onlyAllow);
            $arrDiff   = Arr::only($this->getAllowAbilities(), array_keys($only));
            $arrExcept = Arr::except($this->getAllowValue(), array_values($arrDiff));
            Arr::forget($result, array_keys($arrExcept));

            $types[$key] = $result;
        }

        return $types;
    }

    public function cleanData(): void
    {
        Artisan::call('cache:reset');
    }

    public function getDefaultSettings(): array
    {
        $settings = resolve(Type::class)->getSettings();

        return array_fill_keys($settings, false);
    }

    public function getDisabledSettings(): array
    {
        return [
            Type::CAN_EDIT_TYPE => false,
        ];
    }

    public function isDisabled(string $typeName, string $settingName): bool
    {
        if (!is_array($this->defaultTypes)) {
            return true;
        }

        $type = Arr::get($this->defaultTypes, $typeName);

        if (!is_array($type)) {
            return true;
        }

        $disabledSettings = $this->getDisabledSettings();

        if (!Arr::has($disabledSettings, $settingName)) {
            return false;
        }

        return (bool)Arr::get($type, $settingName, false) == Arr::get($disabledSettings, $settingName);
    }
}
