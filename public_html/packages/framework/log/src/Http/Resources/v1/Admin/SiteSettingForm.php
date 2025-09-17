<?php

namespace MetaFox\Log\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
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
    protected array $variables = [];

    protected function prepare(): void
    {
        $vars = [
            'log.default',
        ];

        $this->variables = [
            'log.default' => config('logging.default'),
        ];

        $values = [];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        foreach ($this->variables as $key => $value) {
            Arr::set($values, $key, $value);
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/log')
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::choice('log.default')
                    ->label(__p('log::phrase.default_channel_label'))
                    ->description(__p('log::phrase.default_channel_desc'))
                    ->required()
                    ->options($this->getLogOptions())
                    ->yup(Yup::string()->required())
            );

        $this->addDefaultFooter(true);
    }

    private function getLogOptions(): array
    {
        $options  = [];
        $channels = config('logging.channels');

        foreach ($channels as $key => $value) {
            if ($value['selectable'] ?? false) {
                $options[] = ['label' => $value['label'] ?? $key, 'value' => $key];
            }
        }

        return $options;
    }

    public function boot(): void
    {
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'log.default' => 'required|string',
        ]);

        $channel = Arr::get($data, 'log.default');

        Log::build(config('logging.channels.' . $channel))
            ->info('Validated log channel ' . $channel);

        return $data;
    }
}
