<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationModule\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use MetaFox\Notification\Repositories\NotificationChannelRepositoryInterface;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string    $appName        = 'notification';
    protected string    $resourceName   = 'module';
    private ?Collection $activeChannels = null;

    /**
     * Get the value of activeChannels.
     */
    public function getActiveChannels(): Collection
    {
        if (null === $this->activeChannels) {
            $this->activeChannels = resolve(NotificationChannelRepositoryInterface::class)->getActiveChannels();
        }

        return $this->activeChannels;
    }

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchModuleForm());

        $this->setDataSource(apiUrl('admin.notification.module.index'));

        $this->addColumn('module_name')
            ->header(__p('core::phrase.package_name'))
            ->flex();

        $this->toggleChannelColumns();

        $this->toggleChannelActions();

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {});
    }

    protected function toggleChannelColumns(): void
    {
        foreach ($this->getActiveChannels() as $channel) {
            if ($channel->isDisable()) {
                continue;
            }

            $this->addColumn("channels.{$channel->name}")
                ->header($channel->title)
                ->asToggle('toggleChannel' . Str::ucfirst($channel->name))
                ->alignCenter()
                ->width(125);
        }
    }

    protected function toggleChannelActions(): void
    {
        $this->withActions(function (Actions $actions) {
            foreach ($this->getActiveChannels() as $channel) {
                if ($channel->isDisable()) {
                    continue;
                }

                $actions->add('toggleChannel' . Str::ucfirst($channel->name))
                    ->apiUrl("admincp/notification/module/channel/$channel->name")
                    ->apiParams([
                        'id' => ':id',
                    ]);
            }
        });
    }
}
