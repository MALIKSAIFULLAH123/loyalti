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
class UpdateSqsQueue extends AbstractForm
{
    protected function prepare(): void
    {
        $res    = $this->resource ?? [];
        $action = sprintf('admincp/queue/connection/%s/%s', $res['driver'] ?? 'sqs', $res['name'] ?? 'sqs');
        $value  = $res['value'] ?? [];

        $this->title(__p('queue::sqs.form_title'))
            ->description(__p('queue::sqs.form_desc'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('key')
                    ->label(__p('queue::sqs.key_label'))
                    ->description(__p('queue::sqs.key_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('secret')
                    ->label(__p('queue::sqs.secret_label'))
                    ->description(__p('queue::sqs.secret_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('region')
                    ->label(__p('queue::sqs.region_label'))
                    ->description(__p('queue::sqs.region_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('prefix')
                    ->label(__p('queue::sqs.prefix_label'))
                    ->description(__p('queue::sqs.prefix_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('queue')
                    ->label(__p('queue::sqs.queue_label'))
                    ->description(__p('queue::sqs.queue_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('suffix')
                    ->label(__p('queue::sqs.suffix_label'))
                    ->description(__p('queue::sqs.suffix_desc'))
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
            'driver' => 'sqs',
        ];
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'key'    => 'required|string',
            'secret' => 'required|string',
            'prefix' => 'required|string',
            'region' => 'required|string',
            'queue'  => 'required|string',
            'suffix' => 'sometimes|string|nullable',
        ]);

        $data['driver'] = 'sqs';

        // try to build with this options

        return $data;
    }
}
