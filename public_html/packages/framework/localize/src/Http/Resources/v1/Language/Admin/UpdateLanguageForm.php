<?php

namespace MetaFox\Localize\Http\Resources\v1\Language\Admin;

use MetaFox\App\Repositories\PackageRepositoryInterface;
use MetaFox\Localize\Models\Language as Model;
use MetaFox\Localize\Repositories\LanguageRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLanguageForm.
 * @property Model $resource
 */
class UpdateLanguageForm extends MakeLanguageForm
{
    public function boot(LanguageRepositoryInterface $repository, int $language)
    {
        $this->resource = $repository->find($language);
    }

    protected function prepare(): void
    {
        $packageId = $this->resource->package_id;
        $package   = null;
        if ($packageId) {
            $package = resolve(PackageRepositoryInterface::class)->getPackageByName($packageId);
        }

        $this->title(__p('localize::language.edit_language'))
            ->action(apiUrl('admin.localize.language.update', ['language' => $this->resource->entityId()]))
            ->asPut()
            ->setValue(
                [
                    '--title'         => $this->resource->name,
                    '--direction'     => $this->resource->direction,
                    '--language_code' => $this->resource->language_code,
                    '--vendor'        => $package?->author ?? 'phpFox',
                ]
            );
    }
}
