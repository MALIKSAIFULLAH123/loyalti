<?php

namespace MetaFox\Log\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StackLoggerForm.
 * @ignore
 * @codeCoverageIgnore
 * @driverType form-logger
 * @driverName stack
 */
class LoggerStackForm extends AbstractForm
{
    protected function prepare(): void
    {
        $res    = $this->resource;
        $driver = Arr::get($res, 'driver', 'slack');
        $name   = Arr::get($res, 'name', 'slack');
        $action = sprintf('/admincp/log/channel/edit/%s/%s', $driver, $name);
        $value  = array_merge(
            ['level' => 'debug'],
            Arr::get($res, 'value', [])
        );

        $this->title(__p('core::phrase.edit'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    private function getStackChannelOptions(): array
    {
        $channels = config('logging.channels');
        $options  = [];

        foreach ($channels as $key => $value) {
            $driver = $value['driver'] ?? 'stream';

            if (in_array($driver, ['null', 'stack', 'daily'])) {
                continue;
            }

            if (in_array($key, ['installation', 'dev', 'null'])) {
                continue;
            }

            $options[] = $key;
        }

        return $options;
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::tags('channels')
                    ->required()
                    ->label(__p('log::phrase.stack_channels_label'))
                    ->description(__p('log::phrase.stack_channels_desc', ['available_channels' => implode(', ', $this->getStackChannelOptions())])),
                Builder::selectLogLevel('level')
                    ->required()
                    ->width(200),
                Builder::checkbox('ignore_exceptions')
                    ->label(__p('log::phrase.ignore_exceptions_checkbox_label')),
            );

        $this->addDefaultFooter();
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'channels.*'        => 'string|required',
            'ignore_exceptions' => 'boolean|sometimes|nullable',
            'level'             => 'string|sometimes|nullable',
        ]);

        $data['driver'] = 'stack';

        return $data;
    }
}
