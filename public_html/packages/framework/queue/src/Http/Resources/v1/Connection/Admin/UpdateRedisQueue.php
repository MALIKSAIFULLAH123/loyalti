<?php

namespace MetaFox\Queue\Http\Resources\v1\Connection\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
class UpdateRedisQueue extends AbstractForm
{
    public const CONFIG_NAME  = 'database.redis.queue';

    protected function prepare(): void
    {
        $res    = $this->resource ?? [];
        $action = sprintf('admincp/queue/connection/%s/%s', $res['driver'] ?? 'redis', $res['name'] ?? 'redis');
        $value  = $res['value'] ?? [];

        $value  = array_merge($value, config(static::CONFIG_NAME, []));

        $this->title(__p('queue::redis.form_title'))
            ->description(__p('queue::redis.form_desc'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('host')
                    ->required()
                    ->label(__p('queue::redis.host_label')),
                Builder::text('port')
                    ->required()
                    ->label(__p('queue::redis.port_label')),
                Builder::text('database')
                    ->required()
                    ->label(__p('queue::redis.database_label')),
                Builder::text('username')
                    ->optional()
                    ->label(__p('core::phrase.username')),
                Builder::text('password')
                    ->optional()
                    ->label(__p('queue::redis.password_label')),
                Builder::text('retry_after')
                    ->label(__p('queue::phrase.retry_after_label'))
                    ->description(__p('queue::phrase.retry_after_desc'))
                    ->yup(Yup::number()->unint()->optional()->nullable()),
                Builder::text('block_for')
                    ->label(__p('queue::phrase.block_for_label'))
                    ->description(__p('queue::phrase.block_for_desc'))
                    ->yup(Yup::string()->optional()->nullable()),
            );

        $this->addDefaultFooter(true);
    }

    public function boot(string $name)
    {
        $value          = Settings::get('queue.connections.' . $name);
        $this->resource = [
            'value'  => $value,
            'name'   => $name,
            'driver' => 'redis',
        ];
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'host'        => 'string|required',
            'port'        => 'string|sometimes|nullable',
            'database'    => 'string|sometimes|nullable',
            'username'    => 'string|sometimes|nullable',
            'password'    => 'string|sometimes|nullable',
            'block_for'   => 'sometimes|int|nullable',
            'retry_after' => 'sometimes|numeric|nullable',
        ]);

        $connectionConfig  = Arr::only($data, [
            'host', 'port', 'database', 'username', 'password', 'block_for', 'retry_after'
        ]);

        Settings::updateSetting(
            'queue',
            'queue.redis',
            static::CONFIG_NAME,
            null,
            $connectionConfig,
            'array',
            false,
            true
        );

        $data['driver']     = 'redis';
        $data['queue']      = 'default';
        $data['connection'] = 'queue';

        return Arr::only($data, ['driver', 'connection', 'queue']);
    }
}
