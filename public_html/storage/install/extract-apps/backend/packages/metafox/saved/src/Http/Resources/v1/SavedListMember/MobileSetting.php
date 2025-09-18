<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedListMember;

use MetaFox\Platform\Resource\WebSetting as Setting;

class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('removeMember')
            ->asDelete()
            ->apiUrl('saveditems-collection/remove-member/:id')
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('saved::phrase.delete_confirm_saved_list_member'),
            ])
            ->apiParams([
                'user_id' => ':user_id',
            ]);
    }
}
