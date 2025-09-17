<?php

namespace MetaFox\Group\Http\Resources\v1\Rule;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Group\Models\Rule;
use MetaFox\Group\Models\Rule as Model;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\ExampleRuleRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\Facades\GroupRule;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreGroupRuleMobileForm.
 * @property Model $resource
 */
class StoreGroupRuleMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(Request $request, GroupRepositoryInterface $repository): void
    {
        $groupId = $request->get('group_id', 0);
        $group   = $repository->find($groupId);
        policy_authorize(GroupPolicy::class, 'update', user(), $group);
        $this->resource = new Rule(['group_id' => $group->entityId()]);
    }

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
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    protected function initialize(): void
    {
        $context = user();
        $this->addBasic()
            ->addFields(
                Builder::hidden('group_id')->required(),
                Builder::chip('rule_example')
                    ->label(__p('group::phrase.example_rule'))
                    ->options(resolve(ExampleRuleRepositoryInterface::class)->getAllActiveRuleExsForForm($context)),
                Builder::text('title')
                    ->required()
                    ->returnKeyType('next')
                    ->label(__p('core::phrase.title'))
                    ->placeholder(__p('core::phrase.fill_in_a_title'))
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->minLength(MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH),
                $this->buildDescriptionField(),
            );
    }

    protected function buildDescriptionField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('description')
                ->label(__p('core::phrase.description'))
                ->variant('standard')
                ->placeholder(__p('core::phrase.fill_in_a_description'))
                ->maxLength(GroupRule::getDescriptionMaxLength())
                ->returnKeyType('next');
        }

        return Builder::textArea('description')
            ->label(__p('core::phrase.description'))
            ->variant('standard')
            ->placeholder(__p('core::phrase.fill_in_a_description'))
            ->maxLength(GroupRule::getDescriptionMaxLength())
            ->returnKeyType('next');
    }
}
