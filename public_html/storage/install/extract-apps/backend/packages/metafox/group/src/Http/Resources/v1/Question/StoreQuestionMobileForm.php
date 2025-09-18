<?php

namespace MetaFox\Group\Http\Resources\v1\Question;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Models\Question;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\Facades\Group as GroupFacade;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * Class StoreQuestionMobileForm.
 * @property Model $resource
 */
class StoreQuestionMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     */
    public function boot(GroupRepositoryInterface $repository, Request $request): void
    {
        $context        = user();
        $groupId        = $request->get('group_id', 0);
        $this->resource = $repository->find($groupId);

        if (!policy_check(GroupPolicy::class, 'addMembershipQuestion', $context, $this->resource)) {
            abort(400, __p('group::phrase.you_have_reached_your_limit_to_add_new_question'));
        }
    }

    protected function prepare(): void
    {
        $this->title(__p('group::phrase.add_question'))
            ->action(url_utility()->makeApiUrl('group-question'))
            ->method(MetaFoxForm::METHOD_POST)
            ->setValue([
                'type_id'  => Question::TYPE_MULTI_SELECT,
                'group_id' => $this->resource->entityId(),
                'options'  => [],
            ]);
    }

    protected function initialize(): void
    {
        $minOption  = Settings::get('group.minimum_membership_question_option', Question::MIN_OPTION);
        $maxOptions = GroupFacade::getMaximumNumberMembershipQuestionOption();
        $basic      = $this->addBasic();

        $basic->addFields(
            Builder::membershipQuestion()
                ->maxLength($maxOptions)
                ->enableWhen(['lt', 'options.length', $maxOptions])
                ->yup(
                    Yup::object()->addProperty(
                        'options',
                        Yup::array()
                            ->max($maxOptions)
                            ->min($minOption)
                    )
                ),
            Builder::hidden('group_id'),
        );
    }
}
