<?php

namespace MetaFox\Page\Http\Resources\v1\PageInvite;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('page-invite')
            ->apiParams(['page_id' => ':id']);

        $this->add('addItem')
            ->apiUrl('core/mobile/form/page.invite.store/:id');

        $this->add('acceptInvite')
            ->apiUrl('page-invite/:id')
            ->asPut()
            ->apiParams(['accept' => 1]);

        $this->add('declineInvite')
            ->apiUrl('page-invite/:id')
            ->asPut()
            ->apiParams(['accept' => 0]);

        $this->add('cancelInvite')
            ->apiUrl('page-invite/:page_id')
            ->asDelete()
            ->apiParams(['user_id' => ':user_id'])
            ->confirm([
                'title'        => __p('page::phrase.confirm_cancel_invite_title'),
                'message'      => 'confirm_cancel_page_invite_desc',
                'phraseParams' => [
                    'userName' => ':user.full_name',
                ],
            ]);
    }
}
