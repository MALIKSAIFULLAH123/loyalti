<?php

namespace MetaFox\Backup\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\User\Models\User;
use MetaFox\User\Support\Facades\UserEntity;

/**
 * Class BackupRunJob.
 */
class BackupProcessJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected int $userId = 0;

    public function __construct(int $userId = 0)
    {
        parent::__construct();
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $filename = Carbon::now()->format('Y-m-d-H-i-s') . '.zip';

        $hasError = Artisan::call('backup:run', ['--filename' => $filename]);

        $output = Artisan::output();
        if ($this->userId == 0) {
            return;
        }

        $user = UserEntity::getById($this->userId);
        $user = $user->detail;

        if (!$user instanceof User) {
            return;
        }

        $email = $user->email;

        if ($email == MetaFoxConstant::EMPTY_STRING) {
            return;
        }

        $filename = config('backup.backup.name') . '/backup-' . $filename;
        $q        = base64_encode($filename);
        $url      = url_utility()->makeApiFullUrl('admincp/backup/file/browse?q=' . $q);
        $mailable = new Mailable();

        if ($hasError) {
            $mailable->error()
                ->subject(__p('backup::phrase.backup_process_failed_subject'))
                ->line(__p('backup::phrase.backup_process_failed_line', [
                    'first_name' => $user->display_name,
                    'error_log'  => $output,
                ]));
        } else {
            $mailable->success()
                ->subject(__p('backup::phrase.backup_process_completed_subject'))
                ->line(__p('backup::phrase.backup_process_completed_line', [
                    'first_name' => $user->display_name,
                ]))
                ->action(__p('core::phrase.view_now'), $url);
        }

        Mail::to($email)->send($mailable);
    }
}
