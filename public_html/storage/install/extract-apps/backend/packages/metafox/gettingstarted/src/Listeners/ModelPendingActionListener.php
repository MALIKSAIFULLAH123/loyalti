<?php

namespace MetaFox\GettingStarted\Listeners;

use MetaFox\GettingStarted\Support\Traits\GettingStartedTrait;
use MetaFox\GettingStarted\Support\Traits\TodoListTrait;
use MetaFox\Platform\MetaFox;

class ModelPendingActionListener
{
    use GettingStartedTrait;
    use TodoListTrait;

    public function handle()
    {
        if (Metafox::isMobile()) {
            return;
        }

        if (!app_active('metafox/gettingstarted')) {
            return;
        }

        $context       = user();
        $resolution    = MetaFox::getResolution();
        $totalTodoList = $this->getTotalTodoList(['resolution' => $resolution]);

        if ($totalTodoList <= 0 || !$this->isFirstLogin($context)) {
            return;
        }

        return [
            'id'          => 'getting_started',
            'title'       => __p('getting-started::phrase.app_name'),
            'description' => '',
            'as'          => 'gettingStarted.dialog.start',
            'reminders'   => [],
            'extra'       => [
                'can_close' => true,
            ],
            'skipDismiss' => true,
        ];
    }
}
