<?php

namespace MetaFox\Core\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;

/**
 * Class SecuritySettingForm.
 */
class SecuritySettingForm extends Form
{
    protected function prepare(): void
    {
        $values = [];
        $vars = [
            'core.security.header_content_type_options',
            'core.security.header_access_control_origin',
        ];

        foreach ($vars as $var) {
            Arr::set($values, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.security'))
            ->action('admincp/setting/core/security')
            ->asPost()
            ->setValue($values);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::alert('gateway_description')
                ->asWarning()
                ->message(__p('core::admin.security_config_warning'))
        );

        $this->addHeadersSection();
        $this->addDefaultFooter(true);
    }

    protected function addHeadersSection()
    {
        $headerSection = $this->addSection([
            'name'  => 'section_security_headers',
            'label' => __p('core::admin.security_headers'),
        ]);

        $headerSection->addFields(
            Builder::alert('typo_security_headers')
                ->asInfo()
                ->message(__p('core::admin.security_headers_desc')),
            Builder::text('core.security.header_content_type_options')
                ->label(__p('core::admin.security_header_content_type_options'))
                ->description(__p('core::admin.security_header_content_type_options_desc')),
            Builder::text('core.security.header_access_control_origin')
                ->label(__p('core::admin.security_header_access_control_origin'))
                ->description(__p('core::admin.security_header_access_control_origin_desc')),
        );
    }

    /**
     * validated.
     *
     * @param  Request      $request
     * @return array<mixed>
     */
    public function validated(Request $request): array
    {
        $params = $request->validate([
            'core.security.header_content_type_options' => 'sometimes|nullable|string',
            'core.security.header_access_control_origin' => 'sometimes|nullable|string',
        ]);

        foreach ($params as $key => $value) {
            if (empty($value)) {
                Arr::set($params, $key, '');
            }
        }

        return $params;
    }
}
