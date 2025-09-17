<?php

namespace MetaFox\Session\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\DriverRepositoryInterface;
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
    private array $variables = [];

    private bool $disabled = false;

    protected function prepare(): void
    {
        $vars = [
            'session.driver',
            'session.lifetime',
            'session.expire_on_close',
            'session.encrypt',
            'session.path',
            'session.domain',
            'session.secure',
            'session.http_only',
            'session.same_site',
            'session.cookie',
        ];

        $values = [];

        $this->variables = [
            'session.driver'   => config('session.driver'),
            'session.lifetime' => config('session.lifetime'),
            'session.path'     => config('session.path'),
            'session.domain'   => config('session.domain'),
            'session.secure'   => config('session.secure'),
            'session.cookie'   => config('session.cookie'),
        ];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        foreach ($this->variables as $key => $value) {
            if (empty($value)) {
                continue;
            }
            Arr::set($values, $key, $value);
        }

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/session')
            ->asPost()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::radioGroup('session.driver')
                    ->label(__p('session::phrase.driver_label'))
                    ->options($this->getSessionDriverOptions())
                    ->disabled(!empty($this->variables['session.driver']))
                    ->description(__p('session::phrase.driver_desc'))
                    ->yup(Yup::string()->required()),
                Builder::text('session.cookie')
                    ->label(__p('session::phrase.cookie_label'))
                    ->description(__p('session::phrase.cookie_desc'))
                    ->disabled(!empty($this->variables['session.cookie']))
                    ->warningExperience()
                    ->yup(Yup::string()->required()),
                Builder::text('session.path')
                    ->label(__p('session::phrase.path_label'))
                    ->description(__p('session::phrase.path_desc'))
                    ->disabled(!empty($this->variables['session.path']))
                    ->warningExperience()
                    ->yup(Yup::string()->optional()->nullable()),
                Builder::text('session.domain')
                    ->label(__p('session::phrase.domain_label'))
                    ->disabled(!empty($this->variables['session.domain']))
                    ->description(__p('session::phrase.domain_desc'))
                    ->warningExperience()
                    ->yup(Yup::string()->optional()->nullable()),
                Builder::radioGroup('session.same_site')
                    ->label(__p('session::phrase.same_site_label'))
                    ->description(__p('session::phrase.same_site_desc'))
                    ->warningExperience()
                    ->options($this->getSessionSameSiteOptions())
                    ->yup(Yup::string()->optional()->nullable()),
                Builder::text('session.lifetime')
                    ->label(__p('session::phrase.lifetime_label'))
                    ->disabled(!empty($this->variables['session.lifetime']))
                    ->description(__p('session::phrase.lifetime_desc'))
                    ->yup(Yup::number()->unint()->required()),
                Builder::checkbox('session.expire_on_close')
                    ->label(__p('session::phrase.expire_on_close_label')),
                Builder::checkbox('session.encrypt')
                    ->label(__p('session::phrase.encrypt_label'))
                    ->label(__p('session::phrase.encrypt_desc')),
                Builder::checkbox('session.secure')
                    ->label(__p('session::phrase.secure_label'))
                    ->description(__p('session::phrase.secure_desc')),
                Builder::checkbox('session.http_only')
                    ->label(__p('session::phrase.http_only_label'))
                    ->description(__p('session::phrase.http_only_desc'))
            );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request)
    {
        return $request->validate([
            'session.driver'          => 'required|string',
            'session.lifetime'        => 'required|int',
            'session.expire_on_close' => 'sometimes|boolean|nullable',
            'session.encrypt'         => 'sometimes|boolean|nullable',
            'session.path'            => 'sometimes|string|nullable',
            'session.domain'          => 'sometimes|string|nullable',
            'session.secure'          => 'sometimes|boolean|nullable',
            'session.http_only'       => 'sometimes|boolean|nullable',
            'session.same_site'       => 'sometimes|string',
            'session.cookie'          => 'required|string',
        ]);
    }

    private function getSessionDriverOptions(): array
    {
        $names = resolve(DriverRepositoryInterface::class)
            ->getNamesHasHandlerClass('form-session');

        sort($names);

        $options = [];
        foreach ($names as $name) {
            $options[] = ['value' => $name, 'label' => __p(sprintf('session::phrase.guide_driver_%s', $name))];
        }

        return $options;
    }

    private function getSessionSameSiteOptions(): array
    {
        return [
            ['value' => 'lax', 'label' => __p('session::phrase.guide_same_site_lax')], ['value' => 'strict', 'label' => __p('session::phrase.guide_same_site_strict')], ['value' => 'none', 'label' => __p('session::phrase.guide_same_site_none')],
        ];
    }
}
