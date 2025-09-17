<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationModule\Admin;

use Illuminate\Support\Collection;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchModuleForm.
 */
class SearchModuleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->noHeader()
            ->action(apiUrl('admin.notification.module.index'))
            ->acceptPageParams(['module_id'])
            ->asGet()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        if ($this->channelsActive()->isEmpty()) {
            $this->addBasic()->addFields(
                Builder::alert('_alert_no_channel_active')
                    ->message(__p('notification::admin.alert_no_channel_active'))
                    ->asInfo(),
            );

            return;
        }

        $this->addBasic(['variant' => 'horizontal'])
            ->asHorizontal()
            ->addFields(
                Builder::selectPackageAlias('module_id')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.package_name')),
                Builder::submit()
                    ->forAdminSearchForm()
            );
    }

    protected function channelsActive(): Collection
    {
        $channelCollects = $this->channelRepository()->getActiveChannels();

        return $channelCollects->filter(function ($channel) {
            return !$channel->isDisable();
        });
    }

    protected function channelRepository(): NotificationChannelRepositoryInterface
    {
        return resolve(NotificationChannelRepositoryInterface::class);
    }
}
