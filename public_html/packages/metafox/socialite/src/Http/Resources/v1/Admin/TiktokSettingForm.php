<?php

namespace MetaFox\Socialite\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class TiktokSettingForm.
 * @codeCoverageIgnore
 * @driverType site-settings
 */
class TiktokSettingForm extends BaseSettingForm
{
    /**
     * @var string
     */
    private $namespace = 'core.services.tiktok';

    protected function prepare(): void
    {
        $vars   = [
            'client_id',
            'client_secret',
            'redirect',
            'login_enabled',
        ];

        $value = [];
        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get("$this->namespace.$var"));
        }

        $this->title(__p('socialite::tiktok.site_settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/socialite.tiktok'))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('client_id')
                    ->label(__p('socialite::tiktok.client_key'))
                    ->required(),
                Builder::text('client_secret')
                    ->label(__p('socialite::tiktok.client_secret'))
                    ->required(),
                Builder::text('redirect')
                    ->label(__p('socialite::tiktok.redirect'))
                    ->description(__p('socialite::tiktok.redirect_desc'))
                    ->required(),
                Builder::checkbox('login_enabled')
                    ->label(__p('socialite::tiktok.login_enabled'))
            );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        return [
            $this->namespace => $request->validate([
                'client_id'     => 'required|string',
                'client_secret' => 'required|string',
                'redirect'      => 'required|string',
                'login_enabled' => 'sometimes|nullable|numeric',
            ]),
        ];
    }
}
