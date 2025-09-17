<?php

namespace MetaFox\Schedule\Http\Resources\v1\Admin;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
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
 * @driverType form-settings
 * @driverName schedule
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        /** @var string[] $vars */
        $vars = [];

        $value = [
            'schedule' => $this->getScheduleCommand(),
            'queue' => $this->getQueueCommand(),
        ];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('schedule::phrase.schedule_settings'))
            ->action('admincp/setting/schedule')
            ->asPost()
            ->setValue($value);
    }

    /**
     * @return string|null
     */
    private function getPhpPath(): ?string
    {
        $pathToPhp = resolve(\Symfony\Component\Process\PhpExecutableFinder::class)->find();

        if ($pathToPhp && is_executable($pathToPhp)) {
            return $pathToPhp;
        }

        return null;
    }

    private function getScheduleCommand(): string
    {
        return sprintf(
            '* * * * *  %s %s/artisan schedule:run',
            $this->getPhpPath(),
            base_path()
        );
    }

    private function getQueueCommand(): string
    {
        return sprintf(
            '*/5 * * * *  %s %s/artisan queue:work --max-time=300',
            $this->getPhpPath(),
            base_path()
        );
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::alert('queue_check')
                    ->asInfo()
                    ->message(__p('schedule::phrase.queue_status_guide', [
                        'health_check_url' => url_utility()->makeApiUrl('/admincp/health-check/wizard')
                    ])),
                Builder::copyText('schedule')
                    ->readOnly()
                    ->label(__P('schedule::phrase.schedule_command'))
                    ->description(__p('schedule::phrase.schedule_command_guide', ['command' => $this->getScheduleCommand()])),
                Builder::copyText('queue')
                    ->readOnly()
                    ->label(__P('schedule::phrase.queue_command'))
                    ->description(__p('schedule::phrase.queue_command_guide', ['command' => $this->getQueueCommand()])),
            );

        $this->addDefaultFooter(true);
    }
}
