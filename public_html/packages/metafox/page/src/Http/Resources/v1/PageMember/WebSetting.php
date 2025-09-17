<?php

namespace MetaFox\Page\Http\Resources\v1\PageMember;

use MetaFox\Page\Models\PageInvite;
use MetaFox\Platform\Resource\WebSetting as Setting;

class WebSetting extends Setting
{
    public function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('page-member')
            ->apiParams(['page_id' => ':id'])
            ->apiRules([
                'q'    => ['truthy', 'q'],
                'view' => ['includes', 'sort', ['all', 'member', 'admin', 'friend']],
            ]);

        $this->add('reassignOwner')
            ->apiUrl('page-member/reassign-owner')
            ->asPut()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);

        $this->add('removeMember')
            ->apiUrl('core/form/page.page_member.remove_member')
            ->asGet()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);

        $this->add('addPageAdmins')
            ->apiUrl('page-member/add-page-admin')
            ->asPost()
            ->apiParams(['page_id' => ':id', 'user_ids' => ':ids']);

        $this->add('removeAsAdmin')
            ->apiUrl('page-member/remove-page-admin')
            ->asDelete()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id', 'is_delete' => 0]);

        $this->add('blockFromPage')
            ->apiUrl('core/form/page.page_block.block_member')
            ->asGet()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);

        $this->add('cancelAdminInvite')
            ->apiUrl('page-member/cancel-invite')
            ->asDelete()
            ->apiParams([
                'page_id'     => ':page_id', 'user_id' => ':user_id',
                'invite_type' => PageInvite::INVITE_ADMIN,
            ]);
    }
}
