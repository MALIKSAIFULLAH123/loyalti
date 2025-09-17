<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Support\Message;
use MetaFox\User\Models\User;
use MetaFox\User\Models\UserVerify;

class VerifyPhoneNumberJob extends AbstractJob
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
        $code         = app('user.verification')->getVerifyCode(UserVerify::ACTION_PHONE_NUMBER);
        $verification = app('user.verification')->generate($this->user, UserVerify::ACTION_PHONE_NUMBER, $this->verifiable, $code);
        if (empty($verification)) {
            return;
        }

        $siteName = Settings::get('core.general.site_name');
        $link     = route('user.verification', [
            'hash' => $verification->hash_code,
        ], true);
        $content  = __p(
            'user::phrase.verify_your_phone_number_body',
            ['site_name' => $siteName, 'code' => $code, 'link' => $link]
        );

        /** @var Message $message */
        $message = resolve(Message::class);
        $message->setContent($content);
        $message->setRecipients($this->verifiable);
        $message->setUrl(null);

        /** @var ManagerInterface $manager */
        $manager = resolve(ManagerInterface::class);
        $manager->service()->send($message);
    }
}
