<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Group\Http\Resources\v1\Question;

use MetaFox\Platform\Resource\WebSetting as Setting;

/**
 *--------------------------------------------------------------------------
 * GroupMember Web Resource Setting
 *--------------------------------------------------------------------------
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting.
 *
 * @ignore
 * @codeCoverageIgnore
 */
class WebSetting extends Setting
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

        $this->add('formStore')
            ->apiUrl('group-question/form')
            ->apiParams(['group_id' => ':id']);

        $this->add('formUpdate')
            ->apiUrl('group-question/form/:id');

        $this->add('viewAnswers')
            ->apiUrl('core/form/group.group_question.view_answers')
            ->asGet()
            ->apiParams([
                'group_id'   => ':group_id',
                'request_id' => ':id',
            ]);
    }
}
