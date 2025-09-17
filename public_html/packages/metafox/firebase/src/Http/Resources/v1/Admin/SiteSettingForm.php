<?php

namespace MetaFox\Firebase\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use MetaFox\Form\Builder;
use MetaFox\Form\AbstractForm;
use MetaFox\Platform\Facades\Settings;

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
    /**
     * @var array<string, mixed>
     */
    private array $variables = [];

    protected function prepare(): void
    {
        $module          = 'firebase';
        $value           = [];
        $this->variables = Arr::dot([$module => config('firebase')]);

        foreach ($this->variables as $var => $val) {
            Arr::set($value, $var, $val ? '**********' : Settings::get($var));
        }

        Arr::set($value, 'firebase.private_key', Settings::get('firebase.private_key'));

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/firebase')
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic      = $this->addBasic();
        $configured = 0;
        foreach ($this->variables as $variable => $value) {
            $keyName = Str::replace('.', '_', $variable);
            $field   = Builder::text($variable)
                ->label(__p(sprintf('firebase::phrase.%s_label', $keyName)))
                ->description(__p(sprintf('firebase::phrase.%s_desc', $keyName)))
                ->disabled((bool) $value);

            if ($value) {
                $configured++;
                $field->warning(__p('core::phrase.setting_configured_in_file'));
            }
            $basic->addField($field);
        }

        $basic->addFields(
            Builder::textArea('firebase.private_key')
                ->label(__p('firebase::phrase.firebase_private_key_label'))
                ->description(__p('firebase::phrase.firebase_private_key_desc')),
        );

        if (count($this->variables) !== $configured) {
            $this->addDefaultFooter(true);
        }
    }
}
