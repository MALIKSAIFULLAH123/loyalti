<?php

namespace MetaFox\Group\Http\Resources\v1\Request;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Support\Browse\Scopes\GroupMember\ViewScope;
use MetaFox\Group\Support\Browse\Scopes\Request\StatusScope;
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
    public bool $isAdminCP = false;

    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'group_id'   => ':id',
        'view'       => ':view',
        'status'     => ':status',
        'q'          => ':q',
        'start_date' => ':start_date',
        'end_date'   => ':end_date',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'group_id'   => ['truthy', 'id'],
        'q'          => ['truthy', 'q'],
        'start_date' => ['truthy', 'start_date'],
        'end_date'   => ['truthy', 'end_date'],
    ];

    public function boot(Request $request): void
    {
        Arr::set($this->apiRules, 'view', ['includes', 'view', ViewScope::getAllowView()]);
        Arr::set($this->apiRules, 'status', ['includes', 'status', StatusScope::getAllowStatus()]);
    }

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [10, 20, 50]);
        $this->setDataSource(apiUrl('group.group-request.index'), $this->apiParams, $this->apiRules);
        $this->setAttribute('allowRiskParams', false);

        $this->addColumn('user.full_name')
            ->header(__p('core::web.member'))
            ->linkTo('user.link')
            ->truncateLines()
            ->flex();

        $this->addColumn('reviewer.full_name')
            ->header(__p('group::phrase.modified_user'))
            ->linkTo('reviewer.link')
            ->truncateLines()
            ->flex();

        $this->addColumn('status_text')
            ->header(__p('core::web.status'))
            ->width(200);

        $this->addColumn('created_at')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('updated_at')
            ->header(__p('group::phrase.modified_date'))
            ->asDateTime()
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('accept')
                ->apiUrl('group-request/:id/accept')
                ->asPatch();

            $actions->add('decline')
                ->apiUrl('core/form/group.group_request.decline/:id')
                ->asGet();

            $actions->add('viewAnswers')
                ->apiUrl('core/form/group.group_question.view_answers')
                ->asGet()
                ->apiParams([
                    'request_id' => ':id',
                ]);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('accept')
                ->value(MetaFoxForm::ACTION_BATCH_ITEM)
                ->label(__p('core::phrase.approve'))
                ->action('accept')
                ->showWhen(['truthy', 'item.extra.can_approve'])
                ->reload();

            $menu->addItem('decline')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('group::phrase.decline'))
                ->action('decline')
                ->showWhen(['truthy', 'item.extra.can_approve'])
                ->reload();

            $menu->addItem('viewReason')
                ->value(MetaFoxForm::ACTION_SHOW_INFO)
                ->label(__p('group::phrase.view_reason'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_view_reason'],
                ])
                ->params([
                    'info'        => [
                        'label' => __p('group::web.group_request_denied_reason'),
                        'field' => 'reason',
                    ],
                    'dialogProps' => [
                        'maxWidth' => 'sm',
                    ],
                ]);

            $menu->addItem('viewAnswers')
                ->action('viewAnswers')
                ->label(__p('group::phrase.view_answers'))
                ->asEditRow();
        });
    }
}
