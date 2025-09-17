<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Page\Http\Resources\v1\PageClaim;

use MetaFox\Page\Support\Facade\PageClaim as PageClaimFacade;
use MetaFox\Page\Support\PageClaimSupport;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('page-claim')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'status'      => ':status',
                'view'        => ':view',
            ])
            ->apiRules([
                'q'           => ['truthy', 'q'],
                'sort'        => ['includes', 'sort', ['recent', 'most_viewed', 'most_member', 'most_discussed']],
                'when'        => ['includes', 'when', ['all', 'this_month', 'this_week', 'today']],
                'category_id' => ['truthy', 'category_id'],
                'status'      => ['includes', 'status', PageClaimFacade::getAllowStatus()],
            ]);

        $this->add('addItem')
            ->asGet()
            ->apiUrl('core/mobile/form/page.claim.store/:id');

        $this->add('editItem')
            ->asGet()
            ->apiUrl('core/mobile/form/page.claim.update/:id');

        $this->add('viewItem')
            ->asGet()
            ->apiUrl('page-claim/:id');

        $this->add('cancelRequest')
            ->apiUrl('page-claim/:id')
            ->asPut()
            ->apiParams(['status' => PageClaimSupport::STATUS_CANCEL]);

        $this->add('resubmit')
            ->apiUrl('page-claim/resubmit/:id')
            ->asPut();

        $this->add('searchItem')
            ->apiUrl('page-claim')
            ->apiParams([
                'q'           => ':q',
                'sort'        => ':sort',
                'when'        => ':when',
                'category_id' => ':category_id',
                'status'      => ':status',
                'view'        => 'search',
            ])
            ->placeholder(__p('page::phrase.search_page_claims'));
    }
}
