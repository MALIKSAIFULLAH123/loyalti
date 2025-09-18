<?php

namespace MetaFox\Story\Http\Resources\v1\BackgroundSet\Admin;

use Illuminate\Support\Arr;
use MetaFox\Platform\Facades\ResourceGate;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Story\Models\BackgroundSet as Model;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateBackgroundSetForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateBackgroundSetForm extends StoreBackgroundSetForm
{
    public function boot(BackgroundSetRepositoryInterface $repository, ?int $id = null)
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values = [
            'title'     => $this->resource->title,
            'is_active' => $this->resource->is_active,
        ];
        $values = $this->prepareAttachedPhotos($values);
        $this->action("admincp/story/background-set/{$this->resource->entityId()}")
            ->asPut()
            ->setValue($values);
    }

    protected function prepareAttachedPhotos(array $values): array
    {
        $items = [];

        $backgrounds = $this->resource->backgrounds()
            ->where('is_deleted', '!=', 1)
            ->get();

        if ($backgrounds->count()) {
            $items = $backgrounds->map(function ($background) {
                $response = ResourceGate::asItem($background, null);
                return array_merge($response->toArray(request()), [
                    'status' => MetaFoxConstant::FILE_UPDATE_STATUS,
                ]);
            });
        }

        Arr::set($values, 'background_temp_file', !empty($items) ? $items->values() : []);

        return $values;
    }
}
