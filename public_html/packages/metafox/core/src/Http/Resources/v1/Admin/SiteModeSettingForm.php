<?php

namespace MetaFox\Core\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Rules\ExistIfGreaterThanZero;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class SiteModeSettingForm.
 */
class SiteModeSettingForm extends Form
{
    protected function prepare(): void
    {
        $vars = [
            'core.offline_message',
            'core.offline_static_page',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        Arr::set($value, 'core.offline', file_exists(base_path('storage/framework/down')));

        $this->title(__p('core::phrase.site_settings'))
            ->action('admincp/setting/core/site-mode')
            ->asPost()
            ->setValue($value);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::checkbox('core.offline')
                ->variant('outlined')
                ->marginNormal()
                ->label(__p('core::phrase.site_is_offline'))
                ->yup(
                    Yup::string()
                ),
            Builder::richTextEditor('core.offline_message')
                ->required()
                ->variant('outlined')
                ->marginNormal()
                ->label(__p('core::phrase.offline_message'))
                ->description(__p('core::phrase.message_that_will_be_displayed_to_guests_when_the_site_is_offline'))
                ->yup(
                    Yup::string()->required()
                ),
            Builder::choice('core.offline_static_page')
                ->label(__p('core::phrase.offline_static_page_title'))
                ->description(__p('core::phrase.offline_static_page_desc'))
                ->alwaysShow()
                ->yup(
                    Yup::number()->nullable()
                )
                ->options($this->staticPageOptions()),
        );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request): array
    {
        $params = $request->validate([
            'core.offline'             => 'sometimes|boolean',
            'core.offline_message'     => 'string|sometimes',
            'core.offline_static_page' => ['nullable', 'numeric', new ExistIfGreaterThanZero('exists:static_pages,id')],
        ]);

        $offline  = Arr::get($params, 'core.offline');
        $modified = $offline != file_exists(base_path('storage/framework/down'));

        if ($modified) {
            if ($offline) {
                Artisan::call('down');
            } else {
                Artisan::call('up');
            }
            Artisan::call('cache:reset');
        }

        return $params;
    }

    public function staticPageOptions(): array
    {
        return resolve(StaticPageRepositoryInterface::class)->getStaticPageOptions();
    }
}
