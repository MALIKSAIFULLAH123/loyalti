<?php

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Form\Section;
use MetaFox\Subscription\Models\SubscriptionComparison as Model;
use MetaFox\Subscription\Repositories\SubscriptionComparisonRepositoryInterface;
use MetaFox\Subscription\Repositories\SubscriptionPackageRepositoryInterface;
use MetaFox\Subscription\Support\Helper;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class CreateSubscriptionComparisonForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateSubscriptionComparisonForm extends AbstractForm
{
    protected function prepare(): void
    {
        $this->title(__p('subscription::admin.create_comparison_feature'))
            ->action(apiUrl('admin.subscription.comparison.store'))
            ->asPost();
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $context = user();

        $packages = resolve(SubscriptionPackageRepositoryInterface::class)->viewPackages($context, ['view' => Helper::VIEW_ADMINCP]);

        if (!$packages->count()) {
            $basic->addField(
                Builder::description('description')
                    ->label(__p('subscription::admin.no_packages_have_been_added_for_comparing_features'))
            );

            return;
        }

        $basic->addFields(
            Builder::text('title')
                ->maxLength(Helper::MAX_COMPARISON_TITLE_LENGTH)
                ->label(__p('subscription::admin.feature_name'))
                ->description(__p(
                    'subscription::admin.maximum_number_characters',
                    ['number' => Helper::MAX_COMPARISON_TITLE_LENGTH]
                ))
                ->required()
                ->yup(
                    Yup::string()
                        ->required()
                        ->setError('typeError', __p('core::validation.name.required'))
                ),
            Builder::description('introduction')
                ->label(__p('subscription::admin.in_which_package_is_this_feature_supported')),
        );

        $this->addPackageFields($basic);

        $this->addDefaultFooter();
    }

    protected function addPackageFields(Section $basic): void
    {
        $context = user();

        $packages = resolve(SubscriptionPackageRepositoryInterface::class)->viewPackages(
            $context,
            ['view' => Helper::VIEW_ADMINCP]
        );

        if (null !== $packages && $packages->count()) {
            foreach ($packages as $key => $package) {
                $varName = 'package_' . $package->entityId() . '_';

                $basic->addFields(
                    Builder::description($varName . 'description')
                        ->label($package->toTitle())
                        ->fullWidth(),
                    Builder::radioGroup($varName . 'type')
                        ->options($this->getTypeOptions())
                        ->yup(
                            Yup::string()
                                ->required()
                                ->setError('required', __p('subscription::admin.you_must_choose_one_option'))
                                ->setError('typeError', __p('subscription::admin.you_must_choose_one_option'))
                        ),
                    Builder::text($varName . 'text')
                        ->maxLength(Helper::MAX_LENGTH_FOR_COMPARISON_TEXT)
                        ->description(__p(
                            'subscription::admin.maximum_number_characters',
                            ['number' => Helper::MAX_LENGTH_FOR_COMPARISON_TEXT]
                        ))
                        ->showWhen([
                            'eq',
                            $varName . 'type',
                            Helper::COMPARISON_TYPE_TEXT,
                        ])
                        ->yup(
                            Yup::string()
                                ->when(
                                    Yup::when($varName . 'type')
                                        ->is(Helper::COMPARISON_TYPE_TEXT)
                                        ->then(
                                            Yup::string()
                                                ->required()
                                                ->maxLength(Helper::MAX_LENGTH_FOR_COMPARISON_TEXT)
                                                ->setError(
                                                    'typeError',
                                                    __p('subscription::validation.content_is_a_required_field')
                                                )
                                                ->setError(
                                                    'required',
                                                    __p('subscription::validation.content_is_a_required_field')
                                                )
                                        )
                                )
                        )
                );

                if ($key < $packages->count() - 1) {
                    $basic->addField(
                        Builder::divider()
                    );
                }
            }
        }
    }

    protected function getTypeOptions(): array
    {
        return [
            [
                'label' => __p('core::phrase.yes'),
                'value' => Helper::COMPARISON_TYPE_YES,
            ],
            [
                'label' => __p('core::phrase.no'),
                'value' => Helper::COMPARISON_TYPE_NO,
            ],
            [
                'label' => __p('subscription::admin.text_field'),
                'value' => Helper::COMPARISON_TYPE_TEXT,
            ],
        ];
    }

    protected function addFooterFields(Section $footer): void
    {
        $this->addDefaultFooter(false);
    }

    /**
     * @param  SubscriptionComparisonRepositoryInterface $repository
     * @param  int|null                                  $comparison
     * @return void
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function boot(SubscriptionComparisonRepositoryInterface $repository, ?int $comparison = null): void
    {
    }
}
