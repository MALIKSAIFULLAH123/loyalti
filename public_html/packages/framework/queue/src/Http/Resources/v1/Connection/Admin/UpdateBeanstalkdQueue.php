<?php

namespace MetaFox\Queue\Http\Resources\v1\Connection\Admin;

use Illuminate\Http\Request;
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
class UpdateBeanstalkdQueue extends AbstractForm
{
    protected function prepare(): void
    {
        $res    = $this->resource ?? [];
        $action = sprintf('admincp/queue/connection/%s/%s', $res['driver'] ?? 'beanstalkd', $res['name'] ?? 'beanstalkd');
        $value  = $res['value'] ?? [];

        $this->title(__p('queue::beanstalkd.form_title'))
            ->description(__p('queue::beanstalkd.form_desc'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('host')
                    ->label(__p('queue::beanstalkd.host_label'))
                    ->description(__p('queue::beanstalkd.host_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('queue')
                    ->label(__p('queue::beanstalkd.queue_label'))
                    ->description(__p('queue::beanstalkd.queue_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('retry_after')
                    ->label(__p('queue::phrase.retry_after_label'))
                    ->description(__p('queue::phrase.retry_after_desc'))
                    ->yup(Yup::number()->unint()->optional()->nullable()),
                Builder::text('block_for')
                    ->label(__p('queue::phrase.block_for_label'))
                    ->description(__p('queue::phrase.block_for_desc'))
                    ->yup(Yup::number()->optional()->nullable()),
            );

        $this->addDefaultFooter(true);
    }

    public function boot(string $name)
    {
        $value          = Settings::get('queue.connections.' . $name);
        $this->resource = [
            'value'  => $value,
            'name'   => $name,
            'driver' => 'beanstalkd',
        ];
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'host'        => 'required|string',
            'queue'       => 'required|string',
            'block_for'   => 'required|int',
            'retry_after' => 'sometimes|int|nullable',
        ]);

        $data['driver'] = 'beanstalkd';

        return $data;
    }
}
