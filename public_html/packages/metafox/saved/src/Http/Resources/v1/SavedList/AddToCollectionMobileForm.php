<?php

namespace MetaFox\Saved\Http\Resources\v1\SavedList;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Saved\Models\SavedList as Model;
use MetaFox\Saved\Repositories\SavedListRepositoryInterface;
use MetaFox\Saved\Repositories\SavedRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class AddToCollectionMobileForm.
 * @property ?Model $resource
 */
class AddToCollectionMobileForm extends AbstractForm
{
    /** @var bool */
    protected $isEdit = false;
    protected int $itemId;

    protected function prepare(): void
    {
        $saved         = $this->savedRepository()->getCollectionByItem($this->itemId);
        $collectionIds = $saved->savedLists->pluck('pivot.list_id')->unique()->toArray();
        $this->title(__p('saved::web.add_to_collections'))
            ->action(url_utility()->makeApiUrl('saveditems/collection'))
            ->asPut()
            ->setValue([
                'item_id'           => $this->itemId,
                'collection_ids'    => !empty($collectionIds) ? $collectionIds : [],
                'show_right_header' => !empty($collectionIds),
            ]);
    }

    public function boot(?int $id): void
    {
        $this->itemId = $id;
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $this->addHeader([
            'showRightHeader' => true,
            'enableWhen'      => [
                'or',
                ['truthy', 'show_right_header'],
                ['and',
                    ['falsy', 'show_right_header'],
                    ['gt', 'collection_ids.length', 0],
                ],
            ],
        ])->component('FormHeader');

        $basic   = $this->addBasic();
        $context = user();
        if ($context->hasPermissionTo('saved_list.create')) {
            $basic->addField(
                Builder::clickable()
                    ->label(__p('saved::phrase.create_new_collection'))
                    ->params(['item_id' => $this->itemId])
                    ->action('addItemToNewCollection'),
            );
        }
        if (count($this->getOptions()) == 0) {
            $basic->description(__p('saved::web.no_description_collections_found'));
        }
        $basic->addFields(
            Builder::choice('collection_ids')
                ->multiple()
                ->label(__p('core::phrase.name'))
                ->options($this->getOptions()),
            Builder::hidden('item_id')
        );
    }

    protected function getOptions(): array
    {
        $saveLists = $this->repository()->getSavedListByUser(user());
        $options   = [];
        foreach ($saveLists as $item) {
            /* @var Model $item */
            $options[] = [
                'label' => $item->name,
                'value' => $item->entityId(),
            ];
        }

        return $options;
    }

    protected function repository(): SavedListRepositoryInterface
    {
        return resolve(SavedListRepositoryInterface::class);
    }

    protected function savedRepository(): SavedRepositoryInterface
    {
        return resolve(SavedRepositoryInterface::class);
    }
}
