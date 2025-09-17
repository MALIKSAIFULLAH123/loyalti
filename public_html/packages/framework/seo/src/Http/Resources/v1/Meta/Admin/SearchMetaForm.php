<?php

namespace MetaFox\SEO\Http\Resources\v1\Meta\Admin;

use Illuminate\Support\Arr;
use MetaFox\App\Models\Package;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\SEO\Models\Meta as Model;
use MetaFox\SEO\Repositories\MetaRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SearchMetaForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SearchMetaForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('core::phrase.edit'))
            ->acceptPageParams(['q', 'package_id', 'resolution'])
            ->action('/')
            ->asPost()
            ->setValue([
                'resolution' => 'web',
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->asHorizontal()
            ->addFields(
                Builder::text('q')
                    ->forAdminSearchForm(),
                Builder::selectPackage('package_id')
                    ->forAdminSearchForm()
                    ->options($this->getPackageOptions()),
                Builder::selectResolution('resolution')
                    ->forAdminSearchForm()
                    ->options([
                        ['label' => __p('core::phrase.web_resolution_label'), 'value' => 'web'],
                        ['label' => __p('core::phrase.admin_resolution_label'), 'value' => 'admin'],
                    ])
                    ->disableClearable(),
                Builder::submit()
                    ->forAdminSearchForm(),
            );
    }

    /**
     * @return array<int, mixed>
     */
    protected function getPackageOptions(): array
    {
        $availablePackages = resolve(MetaRepositoryInterface::class)
            ->getModel()
            ->newModelQuery()
            ->groupBy('package_id')
            ->get('package_id')
            ->collect()
            ->pluck('package_id')
            ->toArray();

        return localCacheStore()->rememberForever(
            __CLASS__ . __METHOD__,
            function () use ($availablePackages) {
                $data = app('core.packages')->getPackageByNames($availablePackages);

                $return = collect($data)
                    ->map(function (Package $package) {
                        return ['label' => $package->title, 'value' => $package->name];
                    })
                    ->values()
                    ->toArray();

                return Arr::sort($return, 'label');
            }
        );
    }
}
