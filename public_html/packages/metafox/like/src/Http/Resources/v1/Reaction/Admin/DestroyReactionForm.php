<?php

namespace MetaFox\Like\Http\Resources\v1\Reaction\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\Section;
use MetaFox\Like\Models\Reaction as Model;
use MetaFox\Like\Repositories\ReactionRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class DestroyReactionForm.
 * @property Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class DestroyReactionForm extends AbstractForm
{
    protected ReactionRepositoryInterface $repository;

    public function boot(ReactionRepositoryInterface $repository, ?int $id = null): void
    {
        $this->repository = $repository;
        $this->resource   = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->title(__p('like::phrase.delete_reaction'))
            ->action(apiUrl('admin.like.reaction.destroy', ['reaction' => $this->resource->entityId()]))
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
     * @return array
     */
    protected function getDeleteOptions(): array
    {
        $options  = $this->repository->getReactionsForConfig();
        $originId = $this->resource->entityId();

        return $options->filter(function (Model $item) use ($originId) {
            return $item->entityId() != $originId;
        })->map(function (Model $item) {
            return [
                'value' => $item->entityId(),
                'label' => $item->title,
            ];
        })->toArray();
    }

    protected function handleConfirm(Section $basic): Section
    {
        $totalItem = $this->resource->total_item;

        if ($totalItem == 0) {
            return $basic->addFields(
                Builder::typography('delete_confirm')
                    ->tagName('strong')
                    ->plainText(__p('like::phrase.delete_reaction_confirm', ['name' => $this->resource->title]))
            );
        }

        $options = $this->getDeleteOptions();

        $basic->addFields(
            Builder::typography('delete_confirm')
                ->tagName('strong')
                ->plainText(__p('like::phrase.delete_reaction_confirm', ['name' => $this->resource->title])),
            Builder::description('delete_notice')
                ->label(__p('core::phrase.action_cant_be_undone')),
            Builder::radioGroup('migrate_items')
                ->label(__p('like::phrase.select_an_action_to_apply_for_all_existing_items_which_were_previously_reacted_with', [
                    'name' => $this->resource->title,
                ]))
                ->options([
                    ['value' => 0, 'label' => __p('like::phrase.delete_all_related_reactions')],
                    ['value' => 1, 'label' => __p('like::phrase.transfer_all_related_reactions_to_the_selected_reaction')],
                ])
                ->yup(Yup::string()->required()),
        );

        if ($options) {
            $basic->addField(Builder::choice('new_reaction_id')
                ->label(__p('like::phrase.reaction'))
                ->requiredWhen(['eq', 'migrate_items', 1])
                ->showWhen(['eq', 'migrate_items', 1])
                ->options($options)
                ->yup(
                    Yup::number()
                        ->positive()
                        ->nullable(true)
                ));
        }

        return $basic;
    }
}
