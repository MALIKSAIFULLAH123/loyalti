<?php

namespace MetaFox\Backup\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $vars = [
            'backup.keep_all_backups_for_days',
            'backup.keep_daily_backups_for_days',
            'backup.keep_weekly_backups_for_weeks',
            'backup.keep_monthly_backups_for_months',
            'backup.keep_yearly_backups_for_years',
            'backup.keep_all_backups_for_days',
            'backup.delete_oldest_backups_when_using_more_megabytes_than',
            'backup.enable_site_backup',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        // override
        Arr::set($value, 'backup.enable_site_backup', $this->siteBackupAllowed() && $this->siteBackupEnabled());

        $this->title(__p('core::phrase.settings'))
            ->action('admincp/setting/backup')
            ->description(__p('backup::phrase.backup_strategy_guide'))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::switch('backup.enable_site_backup')
                    ->label(__p('backup::phrase.enable_site_backup_label'))
                    ->disabled(!$this->siteBackupAllowed()),
                Builder::text('backup.keep_all_backups_for_days')
                    ->label(__p('backup::phrase.keep_all_backups_for_days'))
                    ->showWhen(['truthy', 'backup.enable_site_backup'])
                    //                    ->description(__p('backup::phrase.keep_all_backups_for_days_desc'))
                    ->required()
                    ->yup(Yup::number()->unint()),
                Builder::text('backup.keep_daily_backups_for_days')
                    ->label(__p('backup::phrase.keep_daily_backups_for_days'))
                    ->showWhen(['truthy', 'backup.enable_site_backup'])
                    //                    ->description(__p('backup::phrase.keep_daily_backups_for_days_desc'))
                    ->required()
                    ->yup(Yup::number()->unint()),
                Builder::text('backup.keep_weekly_backups_for_weeks')
                    ->label(__p('backup::phrase.keep_weekly_backups_for_weeks'))
                    ->showWhen(['truthy', 'backup.enable_site_backup'])
                    //                    ->description(__p('backup::phrase.keep_weekly_backups_for_weeks_desc'))
                    ->required()
                    ->yup(Yup::number()->unint()),
                Builder::text('backup.keep_monthly_backups_for_months')
                    ->label(__p('backup::phrase.keep_monthly_backups_for_months'))
                    ->showWhen(['truthy', 'backup.enable_site_backup'])
                    //                    ->description(__p('backup::phrase.keep_monthly_backups_for_months_desc'))
                    ->required()
                    ->yup(Yup::number()->unint()),
                Builder::text('backup.keep_yearly_backups_for_years')
                    ->label(__p('backup::phrase.keep_yearly_backups_for_years'))
                    ->showWhen(['truthy', 'backup.enable_site_backup'])
                    //                    ->description(__p('backup::phrase.keep_yearly_backups_for_years_desc'))
                    ->required()
                    ->yup(Yup::number()->unint()),
                Builder::text('backup.delete_oldest_backups_when_using_more_megabytes_than')
                    ->label(__p('backup::phrase.delete_oldest_backups_when_using_more_megabytes_than'))
                    ->showWhen(['truthy', 'backup.enable_site_backup'])
                    //                    ->description(__p('backup::phrase.delete_oldest_backups_when_using_more_megabytes_than_desc'))
                    ->required()
                    ->yup(Yup::number()->unint()),
            );

        $this->addDefaultFooter(true);
    }

    protected function siteBackupAllowed(): bool
    {
        return (bool) Settings::get('backup.enable_backup', true);
    }

    protected function siteBackupEnabled(): bool
    {
        return (bool) Settings::get('backup.enable_site_backup', true);
    }
}
