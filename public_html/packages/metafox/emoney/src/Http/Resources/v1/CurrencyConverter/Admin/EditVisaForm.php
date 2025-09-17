<?php

namespace MetaFox\EMoney\Http\Resources\v1\CurrencyConverter\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use MetaFox\EMoney\Repositories\CurrencyConverterRepositoryInterface;
use MetaFox\EMoney\Support\Support;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Yup\Yup;

class EditVisaForm extends AbstractForm
{
    protected function prepare(): void
    {
        $config    = $this->resource->config;
        $isSandbox = true;

        if (!is_array($config) || !count($config)) {
            $config = null;
        }

        if (is_array($config)) {
            $isSandbox = Arr::get($config, 'mode', Support::TEST_MODE) == Support::TEST_MODE;
        }

        $this->title(__p('core::phrase.edit'))
            ->asPut()
            ->action('admincp/emoney/conversion-provider/' . $this->resource->service)
            ->setValue([
                'title'      => $this->resource->title,
                'config'     => $config,
                'is_sandbox' => (int) $isSandbox,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::title()
                    ->yup(
                        Yup::string()
                            ->required()
                            ->maxLength(255)
                    ),
                Builder::text('config.api_key')
                    ->label(__p('ewallet::admin.visa_api_key'))
                    ->required()
                    ->yup(
                        Yup::string()
                            ->required(),
                    ),
                Builder::text('config.shared_secret')
                    ->label(__p('ewallet::admin.visa_shared_secret'))
                    ->required()
                    ->yup(
                        Yup::string()
                            ->required(),
                    ),
                Builder::switch('is_sandbox')
                    ->label(__p('ewallet::admin.test_mode'))
                    ->yup(
                        Yup::boolean()
                            ->required(),
                    ),
            );

        $this->addDefaultFooter(true);
    }

    public function boot()
    {
        $this->resource = resolve(CurrencyConverterRepositoryInterface::class)->getConverter(Support::SERVICE_VISA);
    }

    public function validated(Request $request): array
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'config'               => ['required', 'array'],
            'config.api_key'       => ['required', 'string'],
            'config.shared_secret' => ['required', 'string'],
            'is_sandbox'           => ['required', new AllowInRule([0, 1])],
        ]);

        $data = $validator->validate();

        Arr::set($data, 'config.mode', $data['is_sandbox'] ? Support::TEST_MODE : Support::LIVE_MODE);

        unset($data['is_sandbox']);

        return $data;
    }
}
