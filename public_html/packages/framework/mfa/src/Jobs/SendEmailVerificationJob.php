<?php

namespace MetaFox\Mfa\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;

class SendEmailVerificationJob extends AbstractJob implements ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $user, protected string $code)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $siteName = Settings::get('core.general.site_name');

        Mail::to($this->user->email)
            ->send(new Mailable([
                'subject' => __p('mfa::phrase.verification_code_subject'),
                'line'    => __p(
                    'mfa::phrase.verification_code_content',
                    ['site_name' => $siteName, 'code' => $this->code]
                ),
                'user'    => $this->user,
            ]));
    }
}
