<?php

namespace MetaFox\Platform\Contracts;

use Illuminate\Database\Eloquent\Builder;

/**
 * Interface SiteSetting.
 *
 * @see     \MetaFox\Platform\Facades\Settings
 */
interface SiteSettingRepositoryInterface
{
    /**
     * Create/Update core_site_setting by `name`, and `value_default`.
     *
     * @code
     *  $this->create('blog', [
     *      'privacy_enabled'=> ['value'=> true),
     *      'other_name'=> ['value'=>['data2','data3']],
     *      'sample_description'=> ['value'=>"very large text", 'auto' => false]
     * ]);
     * @encode
     *
     * @param string               $module
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    public function setupPackageSettings(string $module, array $settings): array;

    /**
     * @param  string      $module
     * @param  string      $name
     * @param  string|null $configName
     * @param  string|null $envVar
     * @param  mixed       $value
     * @param  string      $type
     * @param  bool|null   $public
     * @param  bool|null   $auto
     * @return bool
     */
    public function updateSetting(string $module, string $name, ?string $configName, ?string $envVar, mixed $value, string $type, bool $public, bool $auto): bool;

    /**
     * @param  string      $module
     * @param  string      $name
     * @param  string|null $configName
     * @param  string|null $envVar
     * @param  mixed       $value
     * @param  string      $type
     * @param  bool        $public
     * @param  bool        $auto
     * @return bool
     */
    public function createSetting(string $module, string $name, ?string $configName, ?string $envVar, mixed $value, string $type, bool $public, bool $auto): bool;

    /**
     * Remove settings from core_site_settings by `module_id` and `name`.
     * If `names` is null, remove all by `module_id`.
     *
     * @code
     *      $this->destroy('blog'); // remove all
     *      $this->destroy('blog', []); // Not remove anything.
     *      $this->destroy('blog',['privacy_enabled']); //
     * @encode
     *
     * @param string            $module
     * @param array<mixed>|null $names
     *
     * @return bool
     */
    public function destroy(string $module, ?array $names = null): bool;

    /**
     * Check a settings is exists.
     *
     * @code
     *      $this->has('blog.privacy_enabled'); // true
     *      $this->has('blog.invalid_key'); // false
     * @encode
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get user actual value for specific setting key.
     * If value_actual is not exists, it use alternate value_default.
     *
     * Note: not all data should be saved in to cached because of data size
     * etc: you should not keep 10Kb text string into setting cache because it push to memory at application start time.
     *
     * @code
     *      $this->get('blog.privacy_enabled'); // true
     *      $this->get('blog.other_name'); // ['data1', 'data2']
     * @encode
     *
     * @param string $key
     *
     * @param mixed $default
     *
     * @return mixed|null
     */
    public function get(string $key, $default = null);

    /**
     * Save user actual values, only existed name in database can be update.
     * It should not create new row.
     *
     * @code
     *      $this->save('blog', [
     *      'privacy_enabled'=>true,
     *      'other_name' => ['data1', 'data2']
     *  ]);
     * @encode
     *
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    public function save(array $values): array;

    /**
     * Save setting values and collect all modified settings by storing in the $modified reference.
     *
     * @param array<string, mixed>      $values
     * @param array<string, mixed>|null $privateSettings
     *
     * @return array<string, mixed>
     */
    public function saveAndCollectModifiedSettings(array $values, ?array &$modified = []): array;

    /**
     * Reset core_site_settings to value_default by specific $module and name list.
     * if $names is null, it will reset all values for specific module.
     *
     * @code
     *      $this->reset('blog', null); // reset all values
     *      $this->reset('blog', []); // not reset.
     *      $this->reset('blog', ['privacy_enabled']); // reset on ly name="privacy_enabled"
     * @endcode
     *
     * @param string            $module
     * @param array<mixed>|null $names
     *
     * @return bool
     */
    public function reset(string $module, ?array $names = null): bool;

    /**
     * This method should be call when you want to refresh cached data after saving new values.
     * By default values is loaded from cached. when refresh call it will reload data from database
     * then pull up to cache.
     *
     * @code
     *      $this->refresh();
     * @endcode
     *
     * @return void
     */
    public function refresh(): void;

    /**
     * Whenever admin updated revision, we must increment versionId
     * This value is used to publish cache and purge caching data cross multiple distribute server.
     *
     * This is alias of $this->get('core.setting_version_id');
     *
     * @code
     *      $this->versionId(); // return 11
     *      setting('core.general.site_title'); // return string
     *      setting('core.general') ; // return array
     *      setting('core'); // null
     * @encode
     *
     * @return int
     */
    public function versionId(): int;

    /**
     * Last updated time. this is alias to `get('core.setting_updated_at')`.
     *
     * @code
     *      $this->updatedAt(); // return "2020-10-04 12:14:12"
     * @encode
     *
     * @return string
     */
    public function updatedAt(): string;

    /**
     * Update laravel config() when booting.
     *
     * @return void
     */
    public function bootingKernelConfigs(): void;

    public function loadConfigValues(): array;

    /**
     * This method is used to generate ide:fix.
     *
     * @return array<mixed>
     */
    public function keys(): array;

    /**
     * @param  string                            $for            web, mobile, admin
     * @param  bool                              $loadFromDriver
     * @return array<string,array<string,mixed>>
     */
    public function getSiteSettings(string $for, bool $loadFromDriver): array;

    public function getPrivateEnvironments(): array;
}
