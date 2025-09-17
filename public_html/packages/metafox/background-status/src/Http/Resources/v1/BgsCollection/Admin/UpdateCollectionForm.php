<?php

namespace MetaFox\BackgroundStatus\Http\Resources\v1\BgsCollection\Admin;

use Illuminate\Support\Arr;
use MetaFox\BackgroundStatus\Models\BgsCollection;
use MetaFox\BackgroundStatus\Models\BgsCollection as Model;
use MetaFox\BackgroundStatus\Repositories\BgsCollectionRepositoryInterface;
use MetaFox\Core\Support\Facades\Language;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateCollectionForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateCollectionForm extends StoreCollectionForm
{
    public function boot(BgsCollectionRepositoryInterface $repository, ?int $id = null)
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values = [
            'title'     => Language::getPhraseValues($this->resource->title_var),
            'is_active' => $this->resource->is_active,
        ];
        $values = $this->prepareAttachedPhotos($values);
        $this->action("admincp/bgs/collection/{$this->resource->entityId()}")
            ->asPut()
            ->setValue($values);
    }

    protected function prepareAttachedPhotos(array $values): array
    {
        $items = [];

        $backgrounds = $this->resource->backgrounds
            ->where('is_deleted', '!=', BgsCollection::IS_DELETED);
        if ($backgrounds->count()) {
            $items = $backgrounds->map(function ($background) {
                $response = ResourceGate::asItem($background, null);
                if (empty($response)) {
                    return [];
                }

                $responseArray = $response->toArray(request());
                if (empty($responseArray)) {
                    return [];
                }

                $responseArray['status'] = MetaFoxConstant::FILE_UPDATE_STATUS;

                return $responseArray;
            })->filter()->toArray();
        }

        Arr::set($values, 'background_temp_file', !empty($items) ? $items : []);

        return $values;
    }
}
