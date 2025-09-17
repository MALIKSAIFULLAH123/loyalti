<?php

namespace MetaFox\Localize\Http\Resources\v1\CountryCity\Admin;

use Illuminate\Http\Request;
use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Localize\Models\CountryChild;
use MetaFox\Localize\Models\CountryCity as Model;
use MetaFox\Localize\Repositories\CountryChildRepositoryInterface;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreCountryCityForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class StoreCountryCityForm extends AbstractForm
{
    protected ?CountryChild $state = null;

    protected bool $isEdit = false;

    public function boot(Request $request): void
    {
        $stateId     = $request->get('state_id', 0);
        $this->state = resolve(CountryChildRepositoryInterface::class)->find($stateId);
    }

    protected function prepare(): void
    {
        $this->title(__p('localize::phrase.add_new_city'))
            ->action(apiUrl('admin.localize.country.city.store'))
            ->asPost()
            ->setValue([
                'state_code' => $this->state?->state_code ?: '0',
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
                Builder::text('city_code')
                    ->required()
                    ->label(__p('localize::country.city_code'))
                    ->yup(Yup::string()->required()->matches('^[1-9]\d+$', __p('validation.integer_without_the', ['attribute' => '${path}']))),
                Builder::hidden('state_code'),
            );

        $this->addDefaultFooter($this->isEdit);
    }
}
