<?php

namespace MetaFox\Profile\Http\Resources\v1\Section\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Profile\Models\Section as Model;
use MetaFox\Profile\Repositories\SectionRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class DestroySectionForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DestroySectionForm extends AbstractForm
{
    protected SectionRepositoryInterface $repository;

    public function boot(SectionRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('profile::phrase.delete_group'))
            ->action("/admincp/profile/section/{$this->resource->id}")
            ->asDelete()
            ->setValue([
                'migrate_items' => 0,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic([]);
        $this->handleConfirm($basic);

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('core::phrase.delete')),
                Builder::cancelButton(),
            );
    }

    /**
     * @return array<string,mixed>
     */
    protected function getSectionOptions(): array
    {
        $options = $this->repository->getSectionByTypeForForm($this->resource->getUserType());

        return array_filter($options, function ($item) {
            return $item['value'] != $this->resource->entityId();
        });
    }

    /**
     * @return array
     */
    protected function getDeleteOptions(): array
    {
        $sectionOptions = $this->getSectionOptions();

        $options[] = [
            'label' => __p('profile::phrase.delete_group_option_delete_all_items'),
            'value' => 0,
        ];

        if (!empty($sectionOptions)) {
            $options[] = [
                'label' => __p('profile::phrase.delete_group_option_move_all_items'),
                'value' => 1,
            ];
        }

        return $options;
    }

    protected function handleConfirm(Section $basic): Section
    {
        $deleteOptions = $this->getDeleteOptions();

        $totalItem = $this->resource->fields->count();
        if ($totalItem == 0) {
            return $basic->addFields(
                Builder::typography('delete_confirm')
                    ->tagName('strong')
                    ->plainText(__p('profile::phrase.delete_group_confirm', ['name' => $this->resource->label]))
            );
        }

        $basic->addFields(
            Builder::typography('delete_confirm')
                ->tagName('strong')
                ->plainText(__p('profile::phrase.delete_group_confirm', ['name' => $this->resource->label])),
            Builder::description('delete_notice')
                ->label(__p('core::phrase.action_cant_be_undone')),
            Builder::radioGroup('migrate_items')
                ->label(__p('profile::phrase.delete_group_option_label'))
                ->options($deleteOptions)
                ->yup(Yup::string()->required()),
        );

        $this->getNewSectionField($basic);

        return $basic;
    }

    protected function getNewSectionField(Section $basic): void
    {
        $sectionOptions = $this->getSectionOptions();

        if (empty($sectionOptions)) {
            return;
        }

        $basic->addField(Builder::choice('new_section_id')
            ->label(__p('profile::phrase.group'))
            ->requiredWhen(['eq', 'migrate_items', 1])
            ->options($sectionOptions)
            ->yup(
                Yup::number()
                    ->positive()
                    ->nullable(true)
            ));
    }

    /**
     * @return string
     */
    protected function getActionUrl(): string
    {
        return '/admincp/profile/section/' . $this->resource->id;
    }
}
