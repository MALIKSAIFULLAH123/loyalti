<?php

namespace MetaFox\Group\Http\Resources\v1\Question;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Group\Models\Question as Model;
use MetaFox\Group\Models\QuestionField;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\QuestionRepositoryInterface;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Yup\Yup;

/**
 * Class UpdateQuestionMobileForm.
 * @property Model $resource
 */
class UpdateQuestionMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(QuestionRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->with(['questionFields'])->find($id);
        policy_authorize(GroupPolicy::class, 'manageMembershipQuestion', $context, $this->resource);
    }

    protected function prepare(): void
    {
        $answers = collect($this->resource->questionFields)
            ->map(function (QuestionField $field) {
                return [
                    'title'  => $field->title,
                    'status' => 'update',
                    'id'     => $field->entityId(),
                ];
            })
            ->values()
            ->toArray();

        $this->title(__p('group::phrase.edit_question'))
            ->action(url_utility()->makeApiUrl('group-question/' . $this->resource->entityId()))
            ->method(MetaFoxForm::METHOD_PUT)
            ->setValue([
                'type_id'  => $this->resource->type_id,
                'question' => $this->resource->question,
                'options'  => $answers,
                'group_id' => $this->resource->group_id,
            ]);
    }

    protected function initialize(): void
    {
        $maxOptions = GroupFacade::getMaximumNumberMembershipQuestionOption();
        $this->addBasic()->addFields(
            Builder::membershipQuestion()
                ->maxLength($maxOptions)
                ->enableWhen(['lt', 'options.length', $maxOptions])
                ->yup(
                    Yup::object()->addProperty(
                        'options',
                        Yup::array()
                            ->max($maxOptions)
                            ->min(Model::MIN_OPTION)
                    )
                ),
            Builder::hidden('group_id'),
        );
    }
}
