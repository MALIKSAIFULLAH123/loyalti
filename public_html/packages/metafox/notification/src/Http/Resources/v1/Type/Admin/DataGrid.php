<?php

namespace MetaFox\Notification\Http\Resources\v1\Type\Admin;

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
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string    $appName        = 'notification';
    protected string    $resourceName   = 'type';
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
        $this->setSearchForm(new SearchTypeForm());

        $this->setDataSource(apiUrl('admin.notification.type.index'), [
            'q'         => ':q',
            'module_id' => ':module_id',
        ]);

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex();

        $this->addColumn('module_name')
            ->header(__p('core::phrase.package_name'))
            ->width(150)
            ->alignCenter();

        $this->toggleChannelColumns();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit']);
        });

        $this->toggleChannelActions();

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }

    protected function toggleChannelColumns()
    {
        foreach ($this->getActiveChannels() as $channel) {
            if ($channel->isDisable()) {
                continue;
            }

            $this->addColumn("active_channels.{$channel->name}")
                ->header($channel->title)
                ->asToggle('toggleChannel' . Str::ucfirst($channel->name))
                ->fieldDisabled("disable_channels.{$channel->name}")
                ->alignCenter()
                ->width(125);
        }
    }

    protected function toggleChannelActions()
    {
        $this->withActions(function (Actions $actions) {
            foreach ($this->getActiveChannels() as $channel) {
                if ($channel->isDisable()) {
                    continue;
                }

                $actions->add('toggleChannel' . Str::ucfirst($channel->name))
                    ->apiUrl("admincp/notification/type/channel/$channel->name")
                    ->urlParams([
                        'id' => ':id',
                    ]);
            }
        });
    }
}
