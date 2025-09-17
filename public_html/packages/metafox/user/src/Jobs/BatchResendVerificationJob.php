<?php

namespace MetaFox\User\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Jobs\AbstractJob;
use MetaFox\User\Policies\UserPolicy;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Support\Facades\UserVerify as UserVerifyFacade;

class BatchResendVerificationJob extends AbstractJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected User $context, protected array $params)
    {
        parent::__construct();
    }

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $action          = Arr::get($this->params, 'action');
        $userIds         = Arr::get($this->params, 'id', []);
        $verifiableField = UserVerifyFacade::getVerifiableField($action);
        $actionService   = UserVerifyFacade::admin($action);
        $userRepository  = resolve(UserRepositoryInterface::class);

        foreach ($userIds as $userId) {
            $user = $userRepository->find($userId);

            if (!$this->shouldResend($user, $verifiableField)) {
                continue;
            }

            $actionService->resend($user, $user->{$verifiableField});
        }
    }

    protected function shouldResend(?User $user, string $verifiableField): bool
    {
        if (!$user) {
            return false;
        }

        if (!policy_check(UserPolicy::class, 'manage', $this->context, $user)) {
            return false;
        }

        if (!$user?->{$verifiableField}) {
            return false;
        }

        return true;
    }
}
