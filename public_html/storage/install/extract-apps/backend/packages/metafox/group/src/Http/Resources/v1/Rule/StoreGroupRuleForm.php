<?php

namespace MetaFox\Group\Http\Resources\v1\Rule;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Group\Models\Rule as Model;
use MetaFox\Group\Repositories\ExampleRuleRepositoryInterface;
use MetaFox\Group\Support\Facades\GroupRule;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreGroupRuleForm.
 * @property Model $resource
 */
class StoreGroupRuleForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->action(url_utility()->makeApiUrl('group-rule'))
            ->title(__p('group::phrase.create_rule'))
            ->method(MetaFoxForm::METHOD_POST)
            ->setValue([
                'group_id' => $this->resource->group_id,
            ]);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::hidden('group_id')->required(),
            $this->handleExampleRuleFields(),
            Builder::text('title')
                ->required()
                ->returnKeyType('next')
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('core::phrase.fill_in_a_title'))
                ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                ->minLength(MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH),
            Builder::textArea('description')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('core::phrase.fill_in_a_description'))
                ->maxLength(GroupRule::getDescriptionMaxLength())
                ->returnKeyType('next'),
        );

        $this->addDefaultFooter(false);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    protected function getOptionsExampleRule()
    {
        return resolve(ExampleRuleRepositoryInterface::class)->getAllActiveRuleExsForForm(user());
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    protected function handleExampleRuleFields(): AbstractField
    {
        $options = $this->getOptionsExampleRule();

        if (empty($options)) {
            return Builder::hidden('rule_example');
        }

        return Builder::chip('rule_example')
            ->label(__p('group::phrase.example_rule'))
            ->options($options);
    }
}
