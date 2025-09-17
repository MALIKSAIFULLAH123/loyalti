<?php

namespace MetaFox\Mfa\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\Sms\Contracts\ManagerInterface;
use MetaFox\Sms\Support\Message;

class SendSmsVerificationJob extends AbstractJob implements ShouldBeUnique
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

        /** @var Message $message */
        $message = resolve(Message::class);
        $message->setContent(__p(
            'mfa::phrase.verification_code_content',
            ['site_name' => $siteName, 'code' => $this->code]
        ));
        $message->setRecipients($this->user->phone_number);
        $message->setUrl(null);

        /** @var ManagerInterface $manager */
        $manager = resolve(ManagerInterface::class);
        $manager->service()->send($message);
    }
}
