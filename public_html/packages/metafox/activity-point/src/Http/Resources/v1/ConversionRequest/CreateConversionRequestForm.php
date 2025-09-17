<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\ConversionRequest;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\ActivityPoint\Form\Html\AmountReceivedField;
use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Models\ConversionRequest as Model;
use MetaFox\ActivityPoint\Policies\ConversionRequestPolicy;
use MetaFox\ActivityPoint\Support\Facade\PointConversion;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateConversionRequestForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateConversionRequestForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('activitypoint::phrase.conversion_request'))
            ->action('activitypoint/conversion-request')
            ->asPost();
    }

    protected function initialize(): void
    {
        $context  = user();
        $currency = app('currency')->getDefaultCurrencyId();

        $availableForConversion = app('events')->dispatch('ewallet.transaction.available_conversion', [$context], true);

        if (!$availableForConversion || !policy_check(ConversionRequestPolicy::class, 'conversionRateForCreate', $currency)) {
            $this->addBasic()
                ->addFields(
                    Builder::description()
                        ->label(__p('activitypoint::phrase.your_account_cannot_send_conversion_requests_at_this_time'))
                );

            return;
        }

        $entityType           = ConversionRequest::ENTITY_TYPE;
        $min                  = PointConversion::getMinPointsCanCreate($context);
        $max                  = PointConversion::getMaxPointsCanCreate($context);
        $maxPerDay            = (int) $context->getPermissionValue(sprintf('%s.max_points_per_day', $entityType));
        $maxPerMonth          = (int) $context->getPermissionValue(sprintf('%s.max_points_per_month', $entityType));
        $currentPoints        = PointConversion::getAvailableUserPoints($context);
        $exchangeRateFormat   = PointConversion::getExchangeRateFormat($currency);
        $commissionPercentage = round(Settings::get('activitypoint.conversion_request_fee', 0), 2);

        if (!policy_check(ConversionRequestPolicy::class, 'createForm', $context)) {
            $this->addBasic()
                ->addFields(
                    Builder::typography('description_1')
                        ->plainText(nl2br(__p('activitypoint::phrase.you_are_not_eligible_for_creating_conversion_request', [
                            'minPoints'      => $min,
                            'hasMaxPerDay'   => $maxPerDay == 0 ? 0 : 1,
                            'hasMaxPerMonth' => $maxPerMonth == 0 ? 0 : 1,
                            'maxPerDay'      => number_format($maxPerDay),
                            'maxPerMonth'    => number_format($maxPerMonth),
                        ]))),
                );
            return;
        }

        $rate = (float) Settings::get(sprintf('activitypoint.conversion_rate.%s', $currency), 0);

        $this->addBasic()
            ->addFields(
                Builder::typography('description_2')
                    ->plainText(__p('activitypoint::phrase.currently_you_have_number_points', [
                        'total'        => $currentPoints,
                        'total_parsed' => number_format($currentPoints),
                    ])),
                Builder::text('points')
                    ->required()
                    ->maxLength(12)
                    ->label(__p('activitypoint::phrase.points'))
                    ->placeholder(__p('activitypoint::phrase.points_placeholder'))
                    ->yup(
                        Yup::number()
                            ->required()
                            ->unint()
                            ->min($min, __p('validation.gte.numeric', [
                                'attribute' => __p('activitypoint::phrase.points'),
                                'value'     => number_format($min),
                            ]))
                            ->max($max, __p('validation.lte.numeric', [
                                'attribute' => __p('activitypoint::phrase.points'),
                                'value'     => number_format($max),
                            ]))
                            ->setError('typeError', __p('activitypoint::phrase.points_format_is_invalid')),
                    ),
                Builder::typography('description_3')
                    ->plainText(nl2br(__p('activitypoint::phrase.conversion_rate_form_description', [
                        'price'            => $exchangeRateFormat,
                        'hasCommissionFee' => $commissionPercentage == 0 ? 0 : 1,
                        'commissionFee'    => number_format($commissionPercentage, 2) . '%',
                    ]))),
                (new AmountReceivedField())
                    ->exchangeRatePattern($currency)
                    ->exchangeRate($rate),
            );

        $this->addDefaultFooter();
    }

    public function boot(): void
    {
        $context = user();

        if ($context->isGuest()) {
            throw new AuthorizationException();
        }

        if (!$context->hasPermissionTo('activitypoint_conversion_request.create')) {
            throw new AuthorizationException();
        }
    }
}
