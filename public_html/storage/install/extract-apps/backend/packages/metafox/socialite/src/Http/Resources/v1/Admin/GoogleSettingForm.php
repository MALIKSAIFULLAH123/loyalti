<?php

namespace MetaFox\Socialite\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class GoogleSettingForm.
 * @ignore
 */
class GoogleSettingForm extends BaseSettingForm
{
    /**
     * @var string
     */
    private $namespace = 'core.services.google';

    protected function prepare(): void
    {
        $vars = [
            'client_id',
            'client_secret',
            'login_enabled',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get("$this->namespace.$var"));
        }

        $this->title(__p('socialite::google.site_settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/socialite.google'))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('client_id')
                    ->label(__p('socialite::google.client_id'))
                    ->required(),
                Builder::text('client_secret')
                    ->label(__p('socialite::google.client_secret'))
                    ->required(),
                Builder::checkbox('login_enabled')
                    ->label(__p('socialite::google.login_enabled'))
            );

        $this->addFooter()
            ->addFields(
                Builder::submit(__p('core::phrase.save_changes'))
            );
    }

    /**
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        return [
            $this->namespace => array_merge($request->validate([
                'client_id'     => 'required|string',
                'client_secret' => 'required|string',
                'login_enabled' => 'sometimes|nullable|numeric',
            ]), [
                'redirect' => config('app.url'),
            ]),
        ];
    }
}
