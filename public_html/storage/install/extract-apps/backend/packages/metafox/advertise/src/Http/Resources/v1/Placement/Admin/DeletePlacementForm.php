<?php

namespace MetaFox\Advertise\Http\Resources\v1\Placement\Admin;

use Illuminate\Support\Arr;
use MetaFox\Advertise\Policies\PlacementPolicy;
use MetaFox\Advertise\Repositories\PlacementRepositoryInterface;
use MetaFox\Advertise\Support\Support;
use MetaFox\Advertise\Support\Facades\Support as Facade;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Advertise\Models\Advertise as Model;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class DeletePlacementForm.
 * @property ?Model $resource
 */
class DeletePlacementForm extends AbstractForm
{
    protected array $alternativePlacements = [];

    protected function prepare(): void
    {
        $values = [
            'id' => $this->resource->entityId(),
        ];

        if ($this->isPermanentlyDelete()) {
            Arr::set($values, 'delete_option', Support::DELETE_PERMANENTLY);
        }

        $this->title(__p('advertise::phrase.delete_placement'))
            ->asPost()
            ->action('admincp/advertise/placement/delete')
            ->setValue($values);
    }

    protected function isPermanentlyDelete(): bool
    {
        if ($this->resource->advertises()->count() == 0) {
            return true;
        }

        if (0 === count($this->alternativePlacements)) {
            return true;
        }

        return false;
    }

    protected function initialize(): void
    {
        $this->addBasicFields();

        $this->addFooter()
            ->addFields(
                Builder::submit()
                    ->label(__p('core::phrase.delete')),
                Builder::cancelButton(),
            );
    }

    protected function getAlternativePlacements(): void
    {
        $this->alternativePlacements = resolve(PlacementRepositoryInterface::class)->getMigrationOptions($this->resource->entityId());
    }

    protected function addBasicFields(): void
    {
        $basic = $this->addBasic();

        $this->getAlternativePlacements();

        $permanentlyDeleteDescription = __p('advertise::phrase.delete_placement_confirm', ['name' => $this->resource->toTitle()]);

        if (0 === count($this->alternativePlacements)) {
            $permanentlyDeleteDescription = __p('advertise::phrase.if_you_delete_this_placement_all_advertises_belong_to_it_will_be_deleted_also');
        }

        $basic->addFields(
            Builder::typography('delete_question')
                ->tagName('strong')
                ->plainText($permanentlyDeleteDescription),
        );

        if ($this->isPermanentlyDelete()) {
            return;
        }

        $basic->addFields(
            Builder::description('delete_notice')
                ->label(__p('core::phrase.action_cant_be_undone')),
            Builder::radioGroup('delete_option')
                ->label(__p('advertise::phrase.delete_option_label'))
                ->required()
                ->options($this->getDeleteOptions())
                ->yup(
                    Yup::string()
                        ->required(__p('authorization::phrase.delete_option_is_a_required_field'))
                        ->setError('typeError', __p('authorization::phrase.delete_option_is_a_required_field'))
                ),
            Builder::choice('alternative_id')
                ->options($this->alternativePlacements)
                ->requiredWhen([
                    'eq',
                    'delete_option',
                    Support::DELETE_MIGRATION,
                ])
                ->showWhen([
                    'eq',
                    'delete_option',
                    Support::DELETE_MIGRATION,
                ])
                ->label(__p('advertise::phrase.alternative_advertise'))
                ->yup(
                    Yup::number()
                        ->when(
                            Yup::when('delete_option')
                                ->is(Support::DELETE_MIGRATION)
                                ->then(
                                    Yup::number()
                                        ->required(__p('advertise::phrase.alternative_advertise_is_a_required_field'))
                                        ->setError('typeError', __p('advertise::phrase.alternative_advertise_is_a_required_field'))
                                )
                        )
                ),
        );
    }

    /**
     * @return array
     */
    protected function getDeleteOptions(): array
    {
        return Facade::getDeleteOptions();
    }

    public function boot(?int $id = null)
    {
        $context = user();

        $this->resource = resolve(PlacementRepositoryInterface::class)->find($id);

        policy_authorize(PlacementPolicy::class, 'delete', $context, $this->resource);
    }
}
