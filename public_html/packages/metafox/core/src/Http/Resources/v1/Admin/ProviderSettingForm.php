<?php

namespace MetaFox\Core\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Core\Models\SiteSetting as Model;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Form\AbstractField;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class GeneralSiteSettingForm.
 * @property Model $resource
 */
class ProviderSettingForm extends Form
{
    private string $namespace = 'core.services';

    /**
     * @property array<string> $serviceKeys
     */
    private array $serviceKeys = [];

    protected function prepare(): void
    {
        $this->initServices();

        $value = [];

        foreach ($this->serviceKeys as $key) {
            $var = sprintf('core.services.%s', $key);

            Arr::set($value, $var, Settings::get($var));
        }

        $this->asPost()
            ->title(__p('core::phrase.service_keys'))
            ->action('admincp/setting/core.provider')
            ->setValue($value);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        foreach ($this->serviceKeys as $key) {
            if (empty($key)) {
                continue;
            }
            $basic->addField($this->buildField($key));
        }

        $this->addDefaultFooter(true);
    }

    protected function initServices(): void
    {
        $this->serviceKeys = localCacheStore()->rememberForever(__METHOD__, function () {
            $defaultKeys = [
                'facebook.app_id',
                'facebook.app_secret',
                'vimeo.client_id',
                'vimeo.client_secret',
                'vimeo.access_token',
                'youtube.api_key',
                'twitter.api_key',
                'twitter.secret_key',
            ];

            $customKeys = app('events')->dispatch('core.provider_setting_service_key');
            $customKeys = collect($customKeys)->flatten()->filter()->values()->toArray();

            return Arr::sort(array_merge($defaultKeys, $customKeys));
        });
    }

    /**
     * @param  string        $service
     * @return AbstractField
     */
    protected function buildField(string $key): AbstractField
    {
        $prefix   = sprintf('%s.%s', $this->namespace, $key);

        $labelKey    = sprintf('core::admin.%s', str_replace('.', '_', $key));
        $label       = __p($labelKey);
        $description = __p($labelKey . '_desc');

        return Builder::text($prefix)
            ->label(($label))
            ->description($description)
            ->optional();
    }

    /**
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $namespace    = 'core.services';
        $defaultValue = Settings::get($namespace, []);

        if (!is_array($defaultValue)) {
            return Arr::undot([$namespace => $defaultValue]);
        }

        $this->initServices();

        $data = $request->all();
        foreach ($this->serviceKeys as $key) {
            $value = Arr::get($data, sprintf('%s.%s', $namespace, $key));
            if ($value) {
                Arr::set($defaultValue, $key, $value);
            }
        }

        return Arr::undot([$namespace => $defaultValue]);
    }
}
