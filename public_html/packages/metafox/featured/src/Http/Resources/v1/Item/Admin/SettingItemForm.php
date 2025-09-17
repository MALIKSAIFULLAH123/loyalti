<?php

namespace MetaFox\Featured\Http\Resources\v1\Item\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Authorization\Models\Permission;
use MetaFox\Featured\Facades\Feature;
use MetaFox\Featured\Services\Contracts\SettingServiceInterface;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Section;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\UserRole;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder as Builder;
use MetaFox\Featured\Models\Item as Model;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SettingItemForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class SettingItemForm extends AbstractForm
{
    /**
     * @var int
     */
    protected int $roleId = UserRole::NORMAL_USER_ID;

    /**
     * @var array
     */
    protected array $settings = [];

    protected array $itemTypeOptions = [];

    /**
     * @var Collection
     */
    protected Collection $permissions;

    public function boot(Request $request)
    {
        $this->roleId = $request->get('role_id', UserRole::NORMAL_USER_ID);

        if (!in_array($this->roleId, Feature::getAllowedRole())) {
            throw new AuthorizationException(__p('phrase.permission_deny'));
        }

        $this->settings = resolve(SettingServiceInterface::class)->getSettings($this->roleId);

        $this->itemTypeOptions = collect(Feature::getApplicableItemTypeOptions())
            ->keyBy('value')
            ->toArray();

        $this->permissions = resolve(SettingServiceInterface::class)->getPermissionsByName($this->settings);
    }

    protected function prepare(): void
    {
        $this->noHeader()
            ->action('admincp/featured/item/setting')
            ->asPost()
            ->setValue([
                'role_id'     => $this->roleId,
                'permissions' => $this->settings,
            ]);
    }

    protected function initialize(): void
    {
        if (!count($this->settings)) {
            $this->addBasic()
                ->addFields(
                    Builder::description()
                        ->label(__p('advertise::phrase.no_settings_available'))
                );

            return;
        }

        foreach ($this->settings as $alias => $setting) {
            $this->addEntityTypeSection($alias, $setting);
        }

        $this->addDefaultFooter(true);
    }

    protected function addEntityTypeSection(string $entityType, array $settings): void
    {
        $fields = [];

        $settings = Arr::dot($settings, $entityType . '.');

        foreach ($settings as $name => $value) {
            /**
             * @var Permission|null $permission
             */
            $permission = $this->permissions->get($name);

            if (null === $permission) {
                continue;
            }

            $fields[] = Builder::switch(sprintf('permissions.%s', $name))
                ->label(__p($permission->getLabelPhrase()))
                ->description(__p($permission->getHelpPhrase()))
                ->marginDense();
        }

        if (!count($fields)) {
            return;
        }

        $this->addSection('entity_' . $entityType)
            ->label(Arr::get($this->itemTypeOptions, sprintf('%s.label', $entityType)))
            ->collapsible()
            ->collapsed()
            ->addFields(...$fields);
    }

    protected function addCurrencyField(Section $section, string $entityType, string $name, ?string $label, ?string $description): void
    {
        $section->addField(
            Builder::description(sprintf('%s_price_description', $entityType))
                ->label($label ?? MetaFoxConstant::EMPTY_STRING)
                ->description($description ?? MetaFoxConstant::EMPTY_STRING)
                ->marginDense()
        );

        foreach ($this->currencies as $currency) {
            $section->addField(
                Builder::text(sprintf('settings_%s_%s', $name, $currency['value']))
                    ->label($currency['label'])
                    ->description(__p('advertise::phrase.specify_amount_you_want_to_charge_people'))
                    ->sizeSmall()
                    ->marginDense()
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->min(0, __p('advertise::phrase.price_must_be_greater_than_or_equal_to_number', ['number' => 0]))
                            ->setError('typeError', __p('advertise::validation.price_must_be_number'))
                    )
            );
        }
    }
}
