<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserVerify;

class VerifyEmailJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $user, protected string $verifiable)
    {
        parent::__construct();
    }

    public function uniqueId(): string
    {
        return __CLASS__ . $this->user->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $code         = app('user.verification')->getVerifyCode(UserVerify::ACTION_EMAIL);
        $verification = app('user.verification')->generate($this->user, UserVerify::ACTION_EMAIL, $this->verifiable, $code);
        if (empty($verification)) {
            return;
        }

        $siteName = Settings::get('core.general.site_name');
        $link     = route('user.verification', [
            'hash' => $verification->hash_code,
        ], true);

        $content = __p(
            'user::phrase.verify_your_email_body',
            ['site_name' => $siteName, 'code' => $code, 'link' => $link]
        );

        Mail::to($this->verifiable)
            ->send(new Mailable([
                'subject' => __p('user::mail.verify_mail_subject'),
                'line'    => $content,
                'user'    => $this->user,
            ]));
    }
}
