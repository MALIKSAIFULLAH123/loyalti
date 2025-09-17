<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationChannel\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchTypeForm.
 */
class SearchChannelForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader();
    }

    protected function initialize(): void
    {
        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::alert('disabled_notification_channels_message')
                    ->asInfo()->message(__p('notification::phrase.disabled_notification_channels_message')),
            );
    }
}
