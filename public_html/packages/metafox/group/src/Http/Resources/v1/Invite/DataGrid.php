<?php

namespace MetaFox\Group\Http\Resources\v1\Invite;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Support\Browse\Scopes\Invite\StatusScope;
use MetaFox\Group\Support\Browse\Scopes\Invite\ViewScope;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;

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
        'group_id'     => ':id',
        'view'         => ':view',
        'status'       => ':status',
        'q'            => ':q',
        'sort'         => ':sort',
        'sort_type'    => ':sort_type',
        'created_from' => ':created_from',
        'created_to'   => ':created_to',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'group_id'     => ['truthy', 'id'],
        'q'            => ['truthy', 'q'],
        'sort'         => ['truthy', 'sort'],
        'sort_type'    => ['truthy', 'sort_type'],
        'created_from' => ['truthy', 'created_from'],
        'created_to'   => ['truthy', 'created_to'],
    ];

    public function boot(Request $request): void
    {
        Arr::set($this->apiRules, 'view', ['includes', 'view', ViewScope::getAllowView()]);
        Arr::set($this->apiRules, 'status', ['includes', 'status', StatusScope::getAllowStatus()]);
    }

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [10, 20, 50]);
        $this->setDataSource(apiUrl('group.group-invite.index'), $this->apiParams, $this->apiRules);
        $this->setAttribute('allowRiskParams', false);
        $this->addColumn('user.full_name')
            ->header(__p('group::phrase.inviter'))
            ->linkTo('user.link')
            ->truncateLines()
            ->flex();

        $this->addColumn('owner.full_name')
            ->header(__p('group::phrase.invitee'))
            ->linkTo('owner.link')
            ->truncateLines()
            ->flex();

        $this->addColumn('invite_label')
            ->header(__p('group::web.invitee_role'))
            ->flex();

        $this->addColumn('status_info')
            ->asColoredText()
            ->header(__p('core::web.status'))
            ->width(200);

        $this->addColumn('expired_at')
            ->header(__p('core::web.expired'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('created_at')
            ->header(__p('group::web.created_date'))
            ->sortableField(SortScope::SORT_DEFAULT)
            ->asDateTime()
            ->sortable()
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('cancelInvite')
                ->apiUrl('/group-invite/:id/cancel')
                ->asPatch();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('cancelInvite')
                ->value(MetaFoxForm::ACTION_BATCH_ITEM)
                ->label(__p('core::phrase.cancel'))
                ->action('cancelInvite')
                ->showWhen(['truthy', 'item.is_pending'])
                ->reload();
        });
    }
}
