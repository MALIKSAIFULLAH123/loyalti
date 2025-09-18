<?php

namespace MetaFox\Group\Http\Resources\v1\Group;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Section;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupChangePrivacyRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;

/**
 * Class PrivacyMobileForm.
 * @property Model $resource
 */
class PrivacyMobileForm extends AbstractForm
{
    protected bool $isPendingChangePrivacy;

    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource               = $repository->find($id);
        $this->isPendingChangePrivacy = resolve(GroupChangePrivacyRepositoryInterface::class)
            ->isPendingChangePrivacy($this->resource);
    }

    protected function prepare(): void
    {
        $url = $this->isPendingChangePrivacy
            ? "group/privacy/change-request/{$this->resource->entityId()}"
            : "group/{$this->resource->entityId()}";

        $this->asPut()
            ->title(__p('group::phrase.group_privacy'))
            ->action($url)
            ->confirm([
                'message' => __p('group::phrase.privacy_change_confirmation'),
            ])
            ->setValue([
                'reg_method' => $this->resource->privacy_type,
            ]);
    }

    protected function initialize(): void
    {
        $this->addHeader(['showRightHeader' => !$this->isPendingChangePrivacy])->component('FormHeader');
        $basic = $this->addBasic();

        $this->handleFieldPrivacy($basic);
    }

    protected function handleFieldPrivacy(Section $basic): AbstractField
    {
        if ($this->isPendingChangePrivacy) {
            return $basic->addFields(
                Builder::typography('typography')
                    ->label(__p('group::phrase.waiting_for_changes_privacy_label'))
                    ->description(__p('group::phrase.waiting_for_changes_privacy_desc'))
                    ->color('text.secondary'),
                Builder::submit()->label(__p('core::phrase.cancel'))
                    ->fullWidth(false),
            );
        }

        $builder = Builder::radioGroup('reg_method')
            ->required()
            ->label(__p('core::phrase.privacy'))
            ->placeholder(__p('core::phrase.privacy'))
            ->description(__p('group::phrase.group_privacy_description'))
            ->options($this->getRegOptions());

        if ($this->resource->isSecretPrivacy()) {
            $builder->setAttributes([
                'isCanEdit'        => false,
                'descriptionValue' => __p('group::phrase.change_privacy_group_secret_description'),
            ]);
        }

        return $basic->addFields($builder);
    }

    protected function getRegOptions(): array
    {
        $currentPrivacy = $this->resource->privacy_type;

        return [
            [
                'value'       => PrivacyTypeHandler::PUBLIC,
                'label'       => __p('group::phrase.public'),
                'description' => __p('group::phrase.anyone_can_see_the_group_its_members_and_their_posts'),
                'disabled'    => $this->checkDisabledPublicPrivacy($currentPrivacy),
            ], [
                'value'       => PrivacyTypeHandler::CLOSED,
                'label'       => __p('group::phrase.closed'),
                'description' => __p('group::phrase.anyone_can_find_the_group_and_see_who_s_in_it_only_members_can_see_posts'),
                'disabled'    => $this->checkDisabledClosedPrivacy($currentPrivacy),
            ], [
                'value'       => PrivacyTypeHandler::SECRET,
                'label'       => __p('group::phrase.secret'),
                'description' => __p('group::phrase.only_members_can_find_the_group_and_see_posts'),
            ],
        ];
    }

    protected function checkDisabledPublicPrivacy(int $privacy): bool
    {
        return $privacy != PrivacyTypeHandler::PUBLIC;
    }

    protected function checkDisabledClosedPrivacy(int $privacy): bool
    {
        return $privacy == PrivacyTypeHandler::SECRET;
    }
}
