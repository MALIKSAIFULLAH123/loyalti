<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryChild\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Localize\Models\Country as Model;
use MetaFox\Localize\Repositories\CountryRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreCountryChildForm.
 *
 * @property Model $resource
 */
class StoreCountryChildForm extends AbstractForm
{
    public function boot(int $id, CountryRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $this->asPost()
            ->title(__p('localize::phrase.add_state'))
            ->action('admincp/localize/country/child')
            ->setValue([
                'country_iso' => $this->resource->country_iso,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('name')
                    ->required()
                    ->label(__p('core::phrase.name'))
                    ->yup(Yup::string()->required()),
                Builder::text('state_iso')
                    ->required()
                    ->maxLength(10)
                    ->label(__p('localize::phrase.state_iso'))
                    ->placeholder(__p('localize::phrase.state_iso'))
                    ->yup(Yup::string()->required()->maxLength(10)),
                Builder::text('state_code')
                    ->required()
                    ->label(__p('localize::phrase.state_code'))
                    ->placeholder(__p('localize::phrase.state_code'))
                    ->yup(Yup::number()
                        ->int()
                        ->required()
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))),
                Builder::text('fips_code')
                    ->maxLength(10)
                    ->label(__p('localize::phrase.fips_code'))
                    ->placeholder(__p('localize::phrase.fips_code'))
                    ->yup(Yup::string()->nullable()->maxLength(10)),
                Builder::text('geonames_code')
                    ->label(__p('localize::phrase.geonames_code'))
                    ->placeholder(__p('localize::phrase.geonames_code'))
                    ->yup(Yup::number()
                        ->nullable()
                        ->int()
                        ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))),
                Builder::hidden('country_iso'),
            );

        $this->addDefaultFooter();
    }
}
