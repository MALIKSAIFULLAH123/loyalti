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
 * Class LoggerSlackForm.
 * @ignore
 * @codeCoverageIgnore
 * @driverType form-logger
 * @driverName slack
 */
class LoggerSlackForm extends AbstractForm
{
    protected function prepare(): void
    {
        $res      = $this->resource;
        $driver   = Arr::get($res, 'driver', 'slack');
        $name     = Arr::get($res, 'name', 'slack');
        $defaults = ['level' => 'critical', 'url' => '', 'emoji' => ':boom:', 'username' => ''];
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
                Builder::text('url')
                    ->required()
                    ->label(__p('log::phrase.slack_url_label'))
                    ->description(__p('log::phrase.slack_url_description')),
                Builder::selectLogLevel('level')
                    ->required()
                    ->width(200),
                Builder::text('username')
                    ->label(__p('log::phrase.slack_username_label'))
                    ->description(__p('log::phrase.slack_username_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('emoji')
                    ->label(__p('log::phrase.slack_emoji_label'))
                    ->description(__p('log::phrase.slack_emoji_desc'))
                    ->yup(Yup::string()->required()),
            );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request): array
    {
        $data = $request->validate([
            'url'      => 'string|required',
            'username' => 'string|required',
            'emoji'    => 'string|required',
            'level'    => 'string|sometimes|nullable',
        ]);

        $data['driver'] = 'slack';

        return $data;
    }
}
