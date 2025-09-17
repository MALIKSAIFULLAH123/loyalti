<?php

namespace MetaFox\Notification\Http\Resources\v1\Type;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Notification\Models\Type as Model;
use MetaFox\Notification\Repositories\NotificationModuleRepositoryInterface;
use MetaFox\Notification\Repositories\TypeRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class MailNotificationFromForm.
 * @property ?Model $resource
 * @driverName notification.mail_settings
 */
class MailNotificationFromForm extends AbstractForm
{
    protected function repository(): NotificationModuleRepositoryInterface
    {
        return resolve(NotificationModuleRepositoryInterface::class);
    }

    protected function typeRepository(): TypeRepositoryInterface
    {
        return resolve(TypeRepositoryInterface::class);
    }

    protected array $modules = [];

    protected function prepare(): void
    {
        $this->modules = $this->repository()->getModulesByChannel();
        $data          = collect($this->modules)->pluck('is_active', 'module_id');
        $this->title(__p('core::phrase.edit'))
            ->action('/')
            ->asPost()
            ->setValue($data);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        foreach ($this->modules as $module) {
            $basic->addFields(
                Builder::switch($module['module_id'])
                    ->label(__p($module['title'])),
            );
        }

        $this->addDefaultFooter();
    }
}
