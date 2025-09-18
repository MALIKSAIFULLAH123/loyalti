<?php

namespace MetaFox\Group\Http\Resources\v1\Rule;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Group\Models\Rule as Model;
use MetaFox\Group\Policies\GroupPolicy;
use MetaFox\Group\Repositories\RuleRepositoryInterface;
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
 * Class UpdateGroupRuleMobileForm.
 * @property Model $resource
 */
class UpdateGroupRuleMobileForm extends AbstractForm
{
    /**
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    public function boot(RuleRepositoryInterface $repository, ?int $id = null): void
    {
        $this->resource = $repository->find($id);
        policy_authorize(GroupPolicy::class, 'update', user(), $this->resource->group);
    }

    protected function prepare(): void
    {
        $this->title(__p('group::phrase.edit_rule'))
            ->action(url_utility()->makeApiResourceUrl('group-rule', $this->resource->entityId()))
            ->asPut()
            ->setValue([
                'title'       => $this->resource->title,
                'description' => $this->resource->description,
            ]);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $context = user();
        $this->addBasic()
            ->addFields(
                Builder::text('title')
                    ->required()
                    ->maxLength(MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH)
                    ->minLength(MetaFoxConstant::DEFAULT_MIN_TITLE_LENGTH)
                    ->label(__p('core::phrase.title'))
                    ->placeholder(__p('core::phrase.fill_in_a_title')),
                $this->buildDescriptionField(),
            );
    }

    protected function buildDescriptionField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('description')
                ->variant('standard')
                ->label(__p('core::phrase.description'))
                ->placeholder(__p('core::phrase.fill_in_a_description'))
                ->maxLength(GroupRule::getDescriptionMaxLength());
        }

        return Builder::textArea('description')
            ->variant('standard')
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('core::phrase.fill_in_a_description'))
            ->maxLength(GroupRule::getDescriptionMaxLength());
    }
}
