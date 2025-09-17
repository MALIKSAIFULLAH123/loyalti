<?php

namespace MetaFox\Core\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use MetaFox\Core\Constants;
use MetaFox\Core\Models\SiteSetting as Model;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Localize\Support\Traits\TranslatableSettingFieldTrait;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class GeneralSiteSettingForm.
 *
 * @property Model $resource
 */
class SiteSettingForm extends Form
{
    use TranslatableSettingFieldTrait;

    protected array $varsTranslatable = [
        'core.general.keywords',
        'core.general.description',
        'core.pwa.app_name',
        'core.pwa.app_description',
        'core.pwa.install_description',
    ];

    protected function prepare(): void
    {
        $vars = [
            'core.general.site_title',
            'core.general.site_name',
            'core.general.title_delim',
            'core.general.site_copyright',
            'core.homepage_url',
            'core.end_head_html',
            'core.start_body_html',
            'core.end_body_html',
            'core.general.title_append',
            'core.general.gdpr_enabled',
            'core.general.enable_2step_verification',
            'core.general.friends_only_community',
            'core.general.min_character_to_search',
            'core.general.start_of_week',
            'core.google.google_map_api_key',
            'core.spam.warning_on_external_links',
            'core.menu_layout_setting',
            'core.pwa.enable',
            'core.general.enforce_display_mode',
            'core.general.allow_html',
            'core.homepage_login_required',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->asPost()
            ->title(__p('core::phrase.site_settings'))
            ->action('admincp/setting/core')
            ->setValue($value);

        $this->prepareTranslatable();
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('core.general.site_name')
                ->label(__p('core::admin.name_of_site_label'))
                ->description(__p('core::admin.name_of_site_desc')),
            Builder::text('core.general.site_title')
                ->label(__p('core::admin.site_title_label'))
                ->description(__p('core::admin.site_title_desc')),
            Builder::switch('core.homepage_login_required')
                ->label(__p('core::admin.homepage_login_required'))
                ->description(__p('core::admin.homepage_login_required_description')),
            Builder::switch('core.pwa.enable')
                ->label(__p('core::admin.enable_pwa'))
                ->description(__p('core::admin.enable_pwa_description')),
            Builder::translatableText('core.pwa.app_name')
                ->label(__p('core::admin.pwa_app_name'))
                ->description(__p('core::admin.pwa_app_name_description'))
                ->showWhen(['truthy', 'core.pwa.enable'])
                ->buildFields(),
            Builder::translatableText('core.pwa.app_description')
                ->label(__p('core::admin.pwa_app_description'))
                ->description(__p('core::admin.pwa_app_description_description'))
                ->showWhen(['truthy', 'core.pwa.enable'])
                ->buildFields(),
            Builder::translatableText('core.pwa.install_description')
                ->asTextArea()
                ->variant('outlined')
                ->marginNormal()
                ->label(__p('core::admin.pwa_install_description'))
                ->description(__p('core::admin.pwa_install_description_description'))
                ->showWhen(['truthy', 'core.pwa.enable'])
                ->buildFields(),
            Builder::dropdown('core.general.start_of_week')
                ->label(__p('core::phrase.week_starts_on'))
                ->options($this->getDayOptions()),
            Builder::text('core.general.site_copyright')
                ->required()
                ->label(__p('core::admin.copyright_label'))
                ->description(__p('core::admin.copyright_desc')),
            Builder::text('core.general.title_delim')
                ->required()
                ->label(__p('core::admin.site_title_delimiter_label'))
                ->description(__p('core::admin.site_title_delimiter_desc')),
            Builder::translatableText('core.general.keywords')
                ->required()
                ->asTextArea()
                ->variant('outlined')
                ->label(__p('core::admin.meta_keywords_label'))
                ->description(__p('core::admin.meta_keywords_desc'))
                ->buildFields(),
            Builder::translatableText('core.general.description')
                ->required()
                ->asTextArea()
                ->variant('outlined')
                ->label(__p('core::admin.meta_description_label'))
                ->description(__p('core::admin.meta_description_desc'))
                ->buildFields(),
            Builder::text('core.homepage_url')
                ->label(__p('core::admin.homepage_url_label'))
                ->description(__p('core::admin.homepage_url_description')),
            //            Builder::text('core.api_url')
            //                ->label(__p('core::admin.api_url_label'))
            //                ->description(__p('core::admin.api_url_desc'))
            //                ->warningExperience()
            //                ->required()
            //                ->yup(Yup::string()->required()),
            //            Builder::text('core.admincp_base_url')
            //                ->label(__p('core::admin.admincp_base_url_label'))
            //                ->description(__p('core::admin.admincp_base_url_desc'))
            //                ->warningExperience()
            //                ->required()
            //                ->yup(Yup::string()->required()),
            Builder::textArea('core.end_head_html')
                ->optional()
                ->variant('outlined')
                ->label(__p('core::admin.append_head_scripts_label'))
                ->description(__p('core::admin.append_head_scripts_desc')),
            Builder::textArea('core.start_body_html')
                ->optional()
                ->variant('outlined')
                ->label(__p('core::admin.prepend_body_scripts_label'))
                ->description(__p('core::admin.prepend_body_scripts_desc')),
            Builder::textArea('core.end_body_html')
                ->optional()
                ->variant('outlined')
                ->label(__p('core::admin.append_body_scripts_label'))
                ->description(__p('core::admin.append_body_scripts_desc')),
            Builder::switch('core.general.gdpr_enabled')
                ->label(__p('core::admin.enable_general_data_protection_regulation_label'))
                ->description(__p('core::admin.enable_general_data_protection_regulation_desc')),
            Builder::switch('core.spam.warning_on_external_links')
                ->label(__p('core::phrase.external_links_warning'))
                ->description(__p('core::phrase.external_links_warning_description')),

            /* TODO: unhide when implementing
             * Builder::switch('core.general.friends_only_community')
                ->required()
                ->variant('outlined')
                ->label('Friends Only Community')
                ->description('By enabling this option certain sections (eg. Blogs, Photos etc...), will by default only show items from the member and his or her friends list.'),*/

            Builder::text('core.general.min_character_to_search')
                ->required()
                ->variant('outlined')
                ->label(__p('core::admin.global_search_minimum_character_label'))
                ->description(__p('core::admin.global_search_minimum_character_desc'))
                ->yup(Yup::number()->int()->min(1)),
            Builder::text('core.google.google_map_api_key')
                ->label(__p('core::admin.google_map_api_key'))
                ->description(__p('core::admin.google_map_api_key_description'))
                ->optional(),
            Builder::choice('core.menu_layout_setting')
                ->label(__p('core::admin.menu_layout_setting'))
                ->description(__p('core::admin.menu_layout_setting_description'))
                ->options($this->getMenuLayoutOptions()),
            Builder::choice('core.general.enforce_display_mode')
                ->label(__p('core::admin.enforce_display_mode'))
                ->options($this->getForceDisplayModeOptions())
                ->description(__p('core::admin.enforce_display_mode_description')),
        /*Builder::switch('core.general.allow_html')
            ->label(__p('core::phrase.allow_html'))
            ->description(__p('core::phrase.allow_html_description')),*/
        );

        $this->addDefaultFooter(true);
    }

    protected function getForceDisplayModeOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.dark_mode'),
                'value' => Constants::FORCE_DISPLAY_DARK_MODE,
            ],
            [
                'label' => __p('core::phrase.light_mode'),
                'value' => Constants::FORCE_DISPLAY_LIGHT_MODE,
            ],
        ];
    }

    /**
     * getDayOptions.
     *
     * @return array<mixed>
     */
    protected function getDayOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.monday'),
                'value' => Carbon::MONDAY,
            ],
            [
                'label' => __p('core::phrase.tuesday'),
                'value' => Carbon::TUESDAY,
            ],
            [
                'label' => __p('core::phrase.wednesday'),
                'value' => Carbon::WEDNESDAY,
            ],
            [
                'label' => __p('core::phrase.thursday'),
                'value' => Carbon::THURSDAY,
            ],
            [
                'label' => __p('core::phrase.friday'),
                'value' => Carbon::FRIDAY,
            ],
            [
                'label' => __p('core::phrase.saturday'),
                'value' => Carbon::SATURDAY,
            ],
            [
                'label' => __p('core::phrase.sunday'),
                'value' => Carbon::SUNDAY,
            ],
        ];
    }

    public function alertMessage($modified = []): ?string
    {
        if (!$settings = Arr::get($modified, 'all')) {
            return null;
        }

        if (Arr::has($settings, 'core.google.google_map_api_key')) {
            return $this->shouldAlertToRebuildMobile() ? $this->rebuildMobileMessage() : $this->rebuildMessage();
        }

        if (
            Arr::hasAny($settings, [
                'core.end_head_html',
                'core.start_body_html',
                'core.end_body_html',
            ])
        ) {
            return $this->rebuildMessage();
        }

        return null;
    }

    private function getMenuLayoutOptions(): array
    {
        return [
            [
                'value' => MetaFoxConstant::LAYOUT_AS_LIST,
                'label' => __p('core::admin.layout_as_list'),
            ],
            [
                'value' => MetaFoxConstant::LAYOUT_AS_GRID,
                'label' => __p('core::admin.layout_as_grid'),
            ],
        ];
    }
}
