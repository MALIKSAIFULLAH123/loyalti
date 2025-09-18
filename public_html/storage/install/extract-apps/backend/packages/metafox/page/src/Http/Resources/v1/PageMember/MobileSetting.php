<?php

namespace MetaFox\Page\Http\Resources\v1\PageMember;

use MetaFox\Page\Models\PageInvite;
use MetaFox\Page\Models\PageMember;
use MetaFox\Platform\MetaFox;
use MetaFox\Platform\Resource\MobileSetting as Setting;

class MobileSetting extends Setting
{
    public function initialize(): void
    {
        $this->add('reassignOwner')
            ->apiUrl('page-member/reassign-owner')
            ->asPut()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);

        $this->add('addPageAdmins')
            ->apiUrl('page-member/add-page-admin')
            ->asPost()
            ->apiParams(['page_id' => ':id', 'user_ids' => ':ids']);

        $this->add('removeAsAdmin')
            ->apiUrl('page-member/remove-page-admin')
            ->asDelete()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id', 'is_delete' => 0]);

        $this->add('viewMembers')
            ->apiUrl('page-member')
            ->apiParams([
                'page_id' => ':id',
                'view'    => 'all',
            ]);

        $this->add('viewAdmins')
            ->apiUrl('page-member')
            ->apiParams([
                'page_id' => ':id',
                'view'    => 'admin',
            ]);

        $this->add('viewFriends')
            ->apiUrl('page-member')
            ->apiParams([
                'page_id' => ':id',
                'view'    => 'friend',
            ]);

        $this->getActionByCheckVersion();

        $this->add('cancelAdminInvite')
            ->apiUrl('page-member/cancel-invite')
            ->asDelete()
            ->apiParams([
                'page_id'     => ':page_id',
                'user_id'     => ':user_id',
                'invite_type' => PageInvite::INVITE_ADMIN,
            ]);

        $this->add('selectAdmins')
            ->apiUrl('page-member')
            ->apiParams([
                'page_id'         => ':id',
                'view'            => 'member',
                'not_invite_role' => PageMember::ADMIN,
                'q'               => ':q',
            ]);
    }

    protected function getActionByCheckVersion(): void
    {
        if (version_compare(MetaFox::getApiVersion(), 'v1.16', '>=')) {
            $this->add('blockFromPage')
                ->apiUrl('core/mobile/form/page.page_block.block_member')
                ->asGet()
                ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);

            $this->add('removeMember')
                ->apiUrl('core/mobile/form/page.page_member.remove_member')
                ->asGet()
                ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);

            return;
        }

        $this->add('blockFromPage')
            ->apiUrl('page-member/remove-page-member')
            ->asDelete()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);

        $this->add('removeMember')
            ->apiUrl('page-member/remove-page-member')
            ->asDelete()
            ->apiParams(['page_id' => ':page_id', 'user_id' => ':user_id']);
    }
}
