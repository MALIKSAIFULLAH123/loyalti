<?php

namespace MetaFox\Search\Support;

use Illuminate\Support\Facades\Cache;
use MetaFox\Search\Contracts\TypeManager as TypeManagerContract;
use MetaFox\Search\Models\Type;

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
     * @var string
     */
    private const CACHE_NAME = 'search_type_manager_cache';

    /**
     * @var int
     */
    private const CACHE_LIFETIME = 3000;

    public function __construct()
    {
        $this->start();
    }

    /**
     * @return void
     */
    protected function start(): void
    {
        if (!$this->types) {
            $this->types = Cache::remember(self::CACHE_NAME, self::CACHE_LIFETIME, function () {
                /**
                 * @var Type[] $types
                 */
                $types = Type::query()
                    ->where([
                        'is_active' => true,
                    ])->get();
                $data = [];
                if (!empty($types)) {
                    foreach ($types as $type) {
                        /** @var string[] $abilities */
                        $abilities = array_keys($type->getAbilities());
                        if (!empty($abilities)) {
                            $data[$type->type]                = $type->toArray();
                            $data[$type->type]['permissions'] = [];
                            foreach ($abilities as $name) {
                                $data[$type->type]['permissions'][$name] = $type->$name;
                            }
                        }
                    }
                }

                return $data;
            });
        }
    }

    /**
     * @param  string $type
     * @return bool
     */
    public function isActive(string $type): bool
    {
        return isset($this->types[$type]);
    }

    /**
     * @param  string $type
     * @param  string $feature
     * @return bool
     */
    public function hasFeature(string $type, string $feature): bool
    {
        if (!$this->isActive($type)) {
            return false;
        }

        if (!isset($this->types[$type]['permissions'][$feature])) {
            return false;
        }

        return $this->types[$type]['permissions'][$feature];
    }

    /**
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function refresh(): void
    {
        cache()->delete(self::CACHE_NAME);
        $this->types = null;
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

        $type->fill([
            'type'        => $data['type'],
            'module_id'   => $data['module_id'],
            'entity_type' => $data['entity_type'],
            'is_active'   => $data['is_active'],
            'title'       => $data['title'],
            'description' => $data['description'],
            'is_system'   => $data['is_system'],
            'params'      => $data['params'] ?? null,
        ]);

        $abilities = $type->getAbilities();

        if (!empty($abilities)) {
            foreach ($abilities as $ability => $key) {
                $value = $data[$ability] ?? false;
                $type->setFlag($key, $value);
            }
        }

        return $type->save() ? $type : false;
    }

    /**
     * @param  string      $type
     * @return string|null
     */
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

    /**
     * @param  string $type
     * @param  string $feature
     * @return bool
     */
    public function hasSetting(string $type, string $feature): bool
    {
        if (!$this->isActive($type)) {
            return false;
        }

        if (!isset($this->types[$type])) {
            return false;
        }

        if (!isset($this->types[$type]['permissions'][$feature])) {
            return false;
        }

        return true;
    }
}
