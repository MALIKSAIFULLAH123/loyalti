<?php

namespace MetaFox\Mfa\Listeners;

use MetaFox\Mfa\Models\EnforceRequest;
use MetaFox\Mfa\Repositories\EnforceRequestRepositoryInterface;
use MetaFox\User\Models\User;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ModelPendingActionListener
{
    public function __construct(
        private EnforceRequestRepositoryInterface $enforceRequestRepository,
    ) {
    }

    public function handle($model)
    {
        if (!$model instanceof User) {
            return;
        }

        $activeRequest = $this->enforceRequestRepository->getActiveRequest($model);
        if (!$activeRequest instanceof EnforceRequest) {
            return;
        }

        return [
            'id' => 'mfa_enforce',
            'title' => __p('mfa::phrase.mfa_setup_required'),
            'description' => __p('mfa::phrase.mfa_setup_required_description', [
                'remaining' => $activeRequest->getRemainingDays(),
            ]),
            'primary_action' => [
                'label' => __p('core::web.get_started'),
                'action' => [
                    'type' => 'navigate',
                    'payload' => [
                        'url' => url_utility()->makeApiFullUrl('settings/mfa'),
                        'target' => '_blank'
                    ],
                ]
            ],
            'extra' => [
                'can_close' => true,
                'can_remind' => true,
            ],
            'reminders' => $activeRequest->getReminderSchedule(),
        ];
    }
}
