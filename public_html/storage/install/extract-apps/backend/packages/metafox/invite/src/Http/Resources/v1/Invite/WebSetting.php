<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Invite\Http\Resources\v1\Invite;

use MetaFox\Invite\Support\Facades\Invite as InviteFacade;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->asGet()
            ->apiUrl('invite')
            ->apiParams([
                'q'          => ':q',
                'status'     => ':status',
                'start_date' => ':start_date',
                'end_date'   => ':end_date',
            ])
            ->apiRules([
                'q'          => ['truthy', 'q'],
                'start_date' => ['truthy', 'start_date'],
                'end_date'   => ['truthy', 'end_date'],
                'status'     => [
                    'includes',
                    'status',
                    InviteFacade::getStatusRules(),
                ],
            ]);

        $this->add('addItem')
            ->asGet()
            ->apiUrl('core/form/invite.store');

        $this->add('resend')
            ->asPut()
            ->apiUrl('invite/resend/:id');

        $this->add('deleteItem')
            ->asDelete()
            ->apiUrl('invite/:id');

        $this->add('batchDeleted')
            ->asDelete()
            ->apiUrl('invite/batch-delete')
            ->apiParams(['id' => ':ids']);

        $this->add('batchResend')
            ->asPatch()
            ->apiUrl('invite/batch-resend')
            ->apiParams(['id' => ':ids']);
        
        $this->add('getGrid')
            ->apiUrl('core/grid/invite');
    }
}
