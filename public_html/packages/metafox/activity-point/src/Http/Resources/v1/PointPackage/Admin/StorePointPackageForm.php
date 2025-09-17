<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin;

use Illuminate\Support\Arr;
use MetaFox\ActivityPoint\Http\Requests\v1\PointPackage\Admin\StoreRequest;
use MetaFox\ActivityPoint\Models\PointPackage as Model;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StorePointPackageForm.
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 * @driverName activitypoint_package.store
 * @driverType form
 */
class StorePointPackageForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.add_new_package'))
            ->action('/admincp/activitypoint/package')
            ->asPost()
            ->setValue([
                'is_active' => 1,
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::text('title')
                ->required()
                ->label(__p(('core::phrase.title')))
                ->description(__p('activitypoint::phrase.maximum_length_the_title_field_desc'))
                ->maxLength(Model::MAXIMUM_PACKAGE_TITLE)
                ->yup(
                    Yup::string()
                        ->required(__p('validation.this_field_is_a_required_field'))
                        ->maxLength(Model::MAXIMUM_PACKAGE_TITLE)
                ),
            Builder::text('amount')
                ->required()
                ->asNumber()
                ->preventScrolling()
                ->label(__p(('activitypoint::phrase.points')))
                ->setAttributes(['minNumber' => 1])
                ->yup(
                    Yup::number()
                        ->int()
                        ->required(__p('activitypoint::validation.points_are_required'))
                        ->min(1)
                        ->max(str_repeat(9, StoreRequest::MAX_AMOUNT_DIGITS))
                        ->setError('max', __p('validation.field_must_be_digits_between', [
                            'min' => StoreRequest::MIN_AMOUNT_DIGITS,
                            'max' => StoreRequest::MAX_AMOUNT_DIGITS,
                        ]))
                ),
            Builder::singlePhoto()
                ->itemType('activitypoint')
                ->previewUrl($this->resource?->image)
                ->placeholder(__p('core::phrase.thumbnail')),
        );
        $this->addPriceFields($basic);

        $basic->addField(
            Builder::checkbox('is_active')->label(__p('core::phrase.is_active')),
        );

        $this->addDefaultFooter($this->resource?->entityId() > 0);
    }

    protected function addPriceFields(Section $basic): void
    {
        $defaultCurrency = app('currency')->getDefaultCurrencyId();
        $currencies      = $this->getCurrencyOptions($defaultCurrency);
        $options         = [];
        $yup             = Yup::object()->required();
        $maxValue        = (int) str_repeat(9, 12);

        foreach ($currencies as $currency) {
            $value = Arr::get($currency, 'value');
            Arr::set($currency, 'value', MetaFoxConstant::EMPTY_STRING);
            Arr::set($currency, 'key', $value);
            Arr::set($currency, 'required', $defaultCurrency == $value);
            $options[] = $currency;

            $subYup = Yup::number()
                ->positive()
                ->setError('positive', __p('activitypoint::validation.field_must_be_a_positive_number', [
                    'attribute' => $currency['label'],
                ]))
                ->max($maxValue, __p('core::validation.currency_must_be_less_than_or_equal_to_number', [
                    'currency_code' => $currency['label'],
                    'number'        => number_format($maxValue),
                ]))
                ->setError('typeError', __p('core::validation.numeric', [
                    'attribute' => $currency['label'],
                ]));

            if ($defaultCurrency == $value) {
                $subYup->required(__p('validation.required', [
                    'attribute' => $currency['label'],
                ]));
            }

            $yup->addProperty($value, $subYup);
        }

        $basic->addField(
            Builder::price('price')
                ->label(__p('core::phrase.price'))
                ->maxLength(12)
                ->marginDense()
                ->sizeSmall()
                ->options($options)
                ->yup($yup)
        );
    }

    protected function getCurrencyOptions(string $defaultCurrency): array
    {
        $currencies = app('currency')->getActiveOptions();

        uasort($currencies, function ($a, $b) use ($defaultCurrency) {
            $aCurrency = Arr::get($a, 'value');

            $bCurrency = Arr::get($b, 'value');

            if ($aCurrency === $defaultCurrency) {
                return -1;
            }

            if ($bCurrency === $defaultCurrency) {
                return 1;
            }

            return 0;
        });

        return array_values($currencies);
    }
}
