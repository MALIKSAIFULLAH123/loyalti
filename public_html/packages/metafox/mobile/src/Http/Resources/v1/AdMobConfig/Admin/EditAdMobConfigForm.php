<?php

namespace MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin;

use Illuminate\Support\Arr;
use MetaFox\Mobile\Models\AdMobConfig as Model;
use MetaFox\Mobile\Models\AdMobPage;
use MetaFox\Mobile\Repositories\AdMobConfigAdminRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreAdMobConfigForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class EditAdMobConfigForm extends StoreAdMobConfigForm
{
    public function boot(AdMobConfigAdminRepositoryInterface $repository, int $id): void
    {
        $this->resource = $repository->getModel()->newModelQuery()->with(['roles', 'pages'])->find($id);
    }

    protected function prepare(): void
    {
        $roles    = collect($this->resource->roles)->pluck('id')->toArray();
        $pages    = collect($this->resource->pages)->pluck('id')->toArray();
        $location = $this->resource->location_priority ?? [];

        $this->title(__p('mobile::phrase.edit_ad_config'))
            ->action(apiUrl('admin.mobile.admob.update', ['admob' => $this->resource->entityId()]))
            ->asPatch()
            ->setValue([
                'name'                    => $this->resource->name,
                'type'                    => $this->resource->type,
                'roles'                   => $roles,
                'pages'                   => $pages,
                'frequency_capping'       => $this->resource->frequency_capping ?? Model::AD_MOB_FREQUENCY_NONE,
                'time_capping_impression' => $this->resource->time_capping_impression ?? 0,
                'time_capping_frequency'  => $this->resource->time_capping_frequency ?? Model::AD_MOB_FREQUENCY_PER_MINUTE,
                'view_capping'            => $this->resource->view_capping ?? 0,
                'location'                => Arr::get($location, 'location', Model::AD_MOB_LOCATION_TOP),
                'is_active'               => (int) $this->resource->is_active,
                'is_sticky'               => (int) $this->resource->is_sticky,
            ]);
    }

    /**
     * Need to get all the options again.
     *
     * @inheritDoc
     */
    protected function getPagesOptions(): array
    {
        $unselectedOptions   = collect(parent::getPagesOptions());
        $unselectedOptionIds = $unselectedOptions->pluck('value')->toArray();

        collect($this->resource->pages)->each(function ($item) use ($unselectedOptionIds, $unselectedOptions) {
            if (!$item instanceof AdMobPage) {
                return;
            }

            if (in_array($item->entityId(), $unselectedOptionIds)) {
                return;
            }

            $unselectedOptions->push([
                'label' => $item->name,
                'value' => $item->entityId(),
            ]);
        });

        return $unselectedOptions->sortBy('value')->values()->toArray();
    }
}
