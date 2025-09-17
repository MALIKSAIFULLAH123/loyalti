<?php

namespace MetaFox\Chat\Http\Resources\v1\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;

class MigrationToChatPlusForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('chat::phrase.migrate_to_chatplus'))
            ->action(apiUrl('admin.chat.setting.migrate-to-chatplus'))
            ->description(__p('chat::phrase.export_data_from_chat_to_chatplus_instruction'))
            ->asPost()
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->sxContainer(['alignItems' => 'flex-start'])
            ->addFields(
                Builder::submit()
                    ->fullWidth(false)
                    ->margin('normal')
                    ->label(__p('chat::phrase.migrate_to_chatplus')),
            );
    }
}
