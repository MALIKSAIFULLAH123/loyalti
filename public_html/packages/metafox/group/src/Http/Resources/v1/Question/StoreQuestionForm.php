<?php

namespace MetaFox\Group\Http\Resources\v1\Question;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * Class StoreQuestionForm.
 */
class StoreQuestionForm extends AbstractForm
{
    protected int $groupId;

    public function __construct(int $groupId, $resource = null)
    {
        parent::__construct($resource);

        $this->groupId = $groupId;
    }

    protected function prepare(): void
    {
        $params = [
            'title'        => __p('group::phrase.add_question'),
            'action'       => url_utility()->makeApiUrl('group-question'),
            'method'       => MetaFoxForm::METHOD_POST,
            'submitAction' => '@group/addNewMembershipQuestion',
            'value'        => [
                'type_id'  => Question::TYPE_MULTI_SELECT,
                'group_id' => $this->groupId,
            ],
        ];

        $this->config($params);
    }

    protected function initialize(): void
    {
        $minOption  = Settings::get('group.minimum_membership_question_option', Question::MIN_OPTION);
        $maxOptions = GroupFacade::getMaximumNumberMembershipQuestionOption();
        $basic      = $this->addBasic();

        $basic->addFields(
            Builder::membershipQuestion()
                ->enableWhen(['lt', 'options.length', $maxOptions])
                ->yup(
                    Yup::object()->addProperty(
                        'options',
                        Yup::array()
                            ->max($maxOptions)
                            ->min($maxOptions)
                    )
                ),
            Builder::hidden('group_id')->setValue($this->groupId),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__('group::phrase.add'))->sizeSmall(),
                Builder::cancelButton()->sizeSmall(),
            );
    }
}
