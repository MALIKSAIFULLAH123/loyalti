<?php

namespace MetaFox\TourGuide\Http\Resources\v1\Step\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\TourGuide\Models\TourGuide;
use MetaFox\TourGuide\Repositories\TourGuideRepositoryInterface;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'tourguide';
    protected string $resourceName = 'step';

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);
        $this->sortable();

        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex(2);

        $this->addColumn('desc')
            ->header(__p('core::phrase.description'))
            ->flex(4);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(200);

        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'toggleActive']);

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl(apiUrl('admin.tourguide.step.order'));
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });
    }

    public function boot(?int $parentId = null): void
    {
        if (!$parentId) {
            return;
        }

        $tourGuide = resolve(TourGuideRepositoryInterface::class)->find($parentId);

        if (!$tourGuide instanceof TourGuide) {
            return;
        }

        $this->withExtraData([
            'tour_guide_id'       => $tourGuide->entityId(),
            'tour_guide_page_url' => $tourGuide->url,
        ]);

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('tourguide::phrase.add_new_step'))
                ->value('tourguide/admin/newStep')
                ->showWhen([
                    'and',
                    ['truthy', 'acl.tourguide.tour_guide.create'],
                ]);
        });
    }
}
