<?php

namespace MetaFox\Socialite\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Socialite\Support\Facades\Apple;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class AppleSettingForm.
 * @codeCoverageIgnore
 */
class AppleSettingForm extends BaseSettingForm
{
    /**
     * @var string
     */
    private $namespace = 'core.services.apple';

    protected function prepare(): void
    {
        $vars      = [
            'team_id',
            'client_id',
            'key_id',
            'private_key',
            'login_enabled',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get("$this->namespace.$var"));
        }

        $this->title(__p('socialite::apple.site_settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/socialite.apple'))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('team_id')
                    ->label(__p('socialite::apple.team_id'))
                    ->required(),
                Builder::text('client_id')
                    ->label(__p('socialite::apple.client_id'))
                    ->required(),
                Builder::text('key_id')
                    ->label(__p('socialite::apple.key_id'))
                    ->required(),
                Builder::textArea('private_key')
                    ->label(__p('socialite::apple.private_key'))
                    ->required(),
                Builder::checkbox('login_enabled')
                    ->label(__p('socialite::apple.login_enabled'))
            );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $data = $request->validate([
            'team_id'       => 'required|string',
            'client_id'     => 'required|string',
            'key_id'        => 'required|string',
            'private_key'   => 'required|string',
            'login_enabled' => 'sometimes|nullable|numeric',
        ]);

        return [
            $this->namespace => array_merge($data, Apple::generateClientSecret($data), [
                'redirect' => config('app.url'),
            ]),
        ];
    }
}
