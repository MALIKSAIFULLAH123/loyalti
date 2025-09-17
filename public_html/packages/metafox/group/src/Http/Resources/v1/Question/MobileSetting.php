<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Question;

use MetaFox\Platform\Resource\MobileSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * GroupMember Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class MobileSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class MobileSetting extends Setting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->apiUrl('group-question')
            ->asPost();

        $this->add('editItem')
            ->apiUrl('group-question/:id')
            ->asPut();

        $this->add('viewAll')
            ->apiUrl('group-question')
            ->apiParams(['group_id' => ':id']);

        $this->add('deleteItem')
            ->apiUrl('group-question/:id')
            ->asDelete()
            ->confirm(
                [
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('group::phrase.delete_confirm_question'),
                ]
            );

        $this->add('getQuestionForm')
            ->apiUrl('group-question/answer-form/:id');

        $this->add('addItemForm')
            ->apiUrl('core/mobile/form/group.group_question.store')
            ->apiParams(['group_id' => ':id']);

        $this->add('editItemForm')
            ->apiUrl('core/mobile/form/group.group_question.update/:id');

        $this->add('getQuestionForm')
            ->apiUrl('core/mobile/form/group.group_question.join/:id');

        $this->add('viewAnswers')
            ->apiUrl('core/mobile/form/group.group_question.view_answers')
            ->asGet()
            ->apiParams([
                'group_id'   => ':group_id',
                'request_id' => ':id',
            ]);
    }
}
