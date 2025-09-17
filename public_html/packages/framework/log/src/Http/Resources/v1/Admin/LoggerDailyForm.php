<?php

namespace MetaFox\Log\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class LoggerDailyForm.
 * @ignore
 * @codeCoverageIgnore
 * @driverType form-logger
 * @driverName daily
 */
class LoggerDailyForm extends AbstractForm
{
    protected function prepare(): void
    {
        $res      = $this->resource;
        $driver   = Arr::get($res, 'driver', 'daily');
        $name     = Arr::get($res, 'name', 'daily');
        $defaults = ['level' => 'debug', 'permission' => '0644', 'days' => 3, 'bubble' => false, 'locking' => false];
        $action   = sprintf('/admincp/log/channel/edit/%s/%s', $driver, $name);
        $value    = array_merge(
            $defaults,
            Arr::get($res, 'value', [])
        );

        $this->title(__p('core::phrase.edit'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('path')
                    ->required()
                    ->label(__p('log::phrase.log_file_label'))
                    ->description(__p('log::phrase.log_file_description')),
                Builder::selectLogLevel('level')
                    ->required()
                    ->width(200),
                Builder::text('days')
                    ->required()
                    ->label(__p('log::phrase.file_rotation_days_label'))
                    ->description(__('log::phrase.file_rotation_days_desc'))
                    ->yup(Yup::number()->required()->unint()),
                Builder::text('permission')
                    ->label(__p('log::phrase.file_permission_label'))
                    ->description(__p('log::phrase.file_permission_desc'))
                    ->yup(Yup::string()->optional()->nullable()),
                Builder::checkbox('bubble')
                    ->label(__p('log::phrase.bubble_log_label'))
                    ->description(__p('log::phrase.bubble_log_desc')),
                Builder::checkbox('locking')
                    ->label(__p('log::phrase.file_locking_label'))
                    ->description(__p('log::phrase.file_locking_desc')),
            );

        $this->addDefaultFooter();
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'path'       => 'string|required',
            'permission' => 'string|sometimes|nullable',
            'locking'    => 'boolean|sometimes|nullable',
            'bubble'     => 'boolean|sometimes|nullable',
            'level'      => 'string|sometimes|nullable',
        ]);

        $data['driver'] = 'daily';

        return $data;
    }
}
