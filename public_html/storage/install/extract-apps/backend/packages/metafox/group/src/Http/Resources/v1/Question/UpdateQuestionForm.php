<?php

namespace MetaFox\Group\Http\Resources\v1\Question;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * Class UpdateQuestionForm.
 */
class UpdateQuestionForm extends AbstractForm
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
            'title'        => __p('group::phrase.edit_question'),
            'action'       => url_utility()->makeApiUrl('group-question/' . $this->resource->entityId()),
            'method'       => MetaFoxForm::METHOD_PUT,
            'submitAction' => '@group/updateMembershipQuestion',
        ];

        $resource = $this->resource;

        $answers = [];

        $fields = $resource->questionFields;

        if (null !== $fields) {
            foreach ($fields as $field) {
                $answers[] = [
                    'title'  => $field->title,
                    'status' => 'update',
                    'id'     => $field->entityId(),
                ];
            }
        }

        Arr::set($params, 'value', [
            'type_id'  => $resource->type_id,
            'question' => $resource->question,
            'options'  => $answers,
        ]);

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
                            ->min($minOption)
                    )
                ),
            Builder::hidden('group_id')->setValue($this->groupId),
        );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__('core::phrase.update'))
                    ->enableWhen([
                        'or',
                        ['eq', 'type_id', Question::TYPE_TEXT],
                        ['gte', 'options.length', $minOption],
                    ])
                    ->sizeSmall(),
                Builder::cancelButton()->sizeSmall()
            );
    }
}
