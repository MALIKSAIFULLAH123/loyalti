<?php

namespace MetaFox\Group\Http\Resources\v1\Group\Setting;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Group\Models\Group as Model;
use MetaFox\Group\Repositories\GroupChangePrivacyRepositoryInterface;
use MetaFox\Group\Repositories\GroupRepositoryInterface;
use MetaFox\Group\Support\PrivacyTypeHandler;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdatePrivacyForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdatePrivacyForm extends AbstractForm
{
    public function boot(GroupRepositoryInterface $repository, ?int $id): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->action("group/{$this->resource->entityId()}")
            ->asPut()
            ->secondAction('group/updateGroupInfo')
            ->setValue([
                'reg_method' => $this->resource->privacy_type,
            ]);
    }

    protected function initialize(): void
    {
        $this->handleFieldPrivacy($this->addBasic());

        $this->addFooter(['separator' => false])
            ->addFields(
                Builder::submit()
                    ->confirmation(['message' => __p('group::phrase.privacy_change_confirmation')])
                    ->disableWhenClean()
                    ->label(__p('core::phrase.save_changes')),
                Builder::cancelButton(),
            );
    }

    protected function handleFieldPrivacy(Section $basic): AbstractField
    {
        /** @var GroupChangePrivacyRepositoryInterface $isPendingChangePrivacy */
        $isPendingChangePrivacy = resolve(GroupChangePrivacyRepositoryInterface::class)
            ->isPendingChangePrivacy($this->resource);

        if ($isPendingChangePrivacy) {
            return $basic->addFields(
                Builder::description('typography')
                    ->label(__p('group::phrase.waiting_for_changes_privacy_label'))
                    ->description(__p('group::phrase.waiting_for_changes_privacy_desc'))
                    ->setAttribute('descriptionProps', [
                        'color' => 'text.hint',
                    ])
                    ->setAttribute('labelProps', [
                        'color' => 'text.primary',
                    ]),
                Builder::htmlLink('cancel')
                    ->label(__p('group::phrase.cancel_request'))
                    ->action('group/cancelChangePrivacy')
                    ->actionPayload([
                        'id' => $this->resource->entityId(),
                    ])
                    ->color('primary'),
            );
        }

        $builder = Builder::radioGroup('reg_method')
            ->required()
            ->label(__p('core::phrase.privacy'))
            ->placeholder(__p('core::phrase.privacy'))
            ->options($this->getRegOptions())
            ->setAttributes([
                'descriptionSingleInput' => __p('group::phrase.group_privacy_description'),
                'reloadOnSubmit'         => true,
            ]);

        if ($this->resource->isSecretPrivacy()) {
            $builder->setAttributes([
                'isCanEdit'        => false,
                'descriptionValue' => __p('group::phrase.change_privacy_group_secret_description'),
            ]);
        }

        return $basic->addField($builder);
    }

    /**
     * @return array<int, mixed>
     */
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

    /**
     * @param int $privacy
     *
     * @return bool
     */
    protected function checkDisabledPublicPrivacy(int $privacy): bool
    {
        return $privacy != PrivacyTypeHandler::PUBLIC;
    }

    /**
     * @param int $privacy
     *
     * @return bool
     */
    protected function checkDisabledClosedPrivacy(int $privacy): bool
    {
        return $privacy == PrivacyTypeHandler::SECRET;
    }
}
