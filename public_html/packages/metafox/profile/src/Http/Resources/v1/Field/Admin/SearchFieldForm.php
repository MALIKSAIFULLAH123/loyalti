<?php

namespace MetaFox\Profile\Http\Resources\v1\Field\Admin;

use MetaFox\Form\AbstractField;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Profile\Support\CustomField as CustomFieldSupport;
use MetaFox\Profile\Support\Facade\CustomField;
use MetaFox\User\Models\User as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchFieldForm.
 *
 * @property Model $resource
 */
class SearchFieldForm extends AbstractForm
{
    protected string $sectionType      = CustomFieldSupport::SECTION_TYPE_USER;
    protected bool   $enableSearchRole = true;

    public function isEnableSearchRole(): bool
    {
        return $this->enableSearchRole;
    }

    public function setEnableSearchRole(bool $enableSearchRole): void
    {
        $this->enableSearchRole = $enableSearchRole;
    }

    public function getSectionType(): string
    {
        return $this->sectionType;
    }

    public function setSectionType(string $sectionType): void
    {
        $this->sectionType = $sectionType;
    }

    protected function prepare(): void
    {
        $this->action('/profile/field')
            ->acceptPageParams([
                'title', 'required', 'active', 'role_id',
            ])
            ->title(__p('core::phrase.edit'))
            ->setValue([]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('title')
                    ->forAdminSearchForm()
                    ->label(__p('core::phrase.name')),
                $this->getRoleField(),
                Builder::choice('active')
                    ->forAdminSearchForm()
                    ->label(__p('profile::phrase.active'))
                    ->options($this->getActiveOptions()),
                Builder::choice('required')
                    ->forAdminSearchForm()
                    ->label(__p('profile::phrase.required'))
                    ->options($this->getRequiredOptions()),
                Builder::submit()
                    ->forAdminSearchForm(),
            );
    }

    private function getActiveOptions(): array
    {
        return
            [
                [
                    'label' => __p('profile::phrase.active'),
                    'value' => 1,
                ],
                [
                    'label' => __p('profile::phrase.inactive'),
                    'value' => 0,
                ],
            ];
    }

    private function getRequiredOptions(): array
    {
        return
            [
                [
                    'label' => __p('core::phrase.yes'),
                    'value' => 1,
                ],
                [
                    'label' => __p('core::phrase.no'),
                    'value' => 0,
                ],
            ];
    }

    protected function getRoleField(): ?AbstractField
    {
        if ($this->getSectionType() != CustomFieldSupport::SECTION_TYPE_USER) {
            return null;
        }

        if (!$this->enableSearchRole) {
            return null;
        }
        return Builder::choice('role_id')
            ->forAdminSearchForm()
            ->label(__p('core::phrase.role'))
            ->options(CustomField::getAllowedRoleOptions());
    }
}
