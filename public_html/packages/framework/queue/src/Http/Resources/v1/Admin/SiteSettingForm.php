<?php

namespace MetaFox\Queue\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    private array $variables = [];

    protected function prepare(): void
    {
        $vars = [
            'queue.default',
            'queue.retry_timeout',
        ];

        $values = [];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        $this->variables = [
            'queue.default' => config('queue.default'),
        ];

        foreach ($this->variables as $key => $value) {
            Arr::set($values, $key, $value);
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/queue')
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $default = $this->variables['queue.default'];

        $options = $this->getQueueOptions();

        if (is_string($default) && MetaFoxConstant::EMPTY_STRING != $default && empty($options)) {
            $options = Arr::prepend($options, [
                'value' => $default,
                'label' => Str::ucfirst($default),
            ]);
        }

        $this->addBasic()
            ->addFields(
                Builder::choice('queue.default')
                    ->options($options)
                    ->label(__p('queue::phrase.default_label'))
                    ->description(__p('queue::phrase.default_desc')),
                Builder::text('queue.retry_timeout')
                    ->label(__p('queue::phrase.time_retry_after_queue_timeout'))
                    ->asNumber()
                    ->yup(Yup::number())
            );

        $this->addDefaultFooter(true);
    }

    private function getQueueOptions(): array
    {
        $options           = [];
        $defaultSelectable = [
            'database',
            'sync',
        ];

        foreach (config('queue.connections') as $key => $config) {
            if (!in_array($key, $defaultSelectable) && !Arr::get($config, 'selectable')) {
                continue;
            }

            $options[] = [
                'value' => $key,
                'label' => __p("queue::$key.name"),
            ];
        }

        return $options;
    }
}
