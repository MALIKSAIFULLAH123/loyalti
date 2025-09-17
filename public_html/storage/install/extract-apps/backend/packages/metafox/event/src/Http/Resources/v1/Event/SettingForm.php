<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Form\FormField;
use MetaFox\Form\Html\Dropdown;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;

/**
 * Class SettingForm.
 * @property int               $id       user id
 * @property array<int, mixed> $settings the list of settings
 */
class SettingForm extends AbstractForm
{
    private int $id;
    private User $user;

    /**
     * @var array<int, mixed>
     */
    private array $settings;

    /**
     * @param  array<int, mixed>       $settings
     * @param  int                     $id
     * @param  null                    $resource
     * @throws AuthenticationException
     */
    public function __construct(array $settings, int $id, $resource = null)
    {
        parent::__construct($resource);

        $this->id       = $id;
        $this->settings = $settings;
        $this->user     = user();
    }

    protected function prepare(): void
    {
        $value = collect($this->settings)->pluck('value', 'var_name')->toArray();

        $this
            ->title(__('event::phrase.event_settings'))
            ->action('event/setting/' . $this->id)
            ->asPut()
            ->preventReset()
            ->setValue(array_merge($value, [
                'pending_mode' => $this->resource?->pending_mode,
            ]));
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        foreach ($this->settings as $setting) {
            $fields = $this->buildFields($setting);
            if (!empty($fields)) {
                $basic->addFields(...$fields);
            }
        }

        $this->addDefaultFooter(true);
    }

    /**
     * @param  array<mixed> $setting
     * @return array<mixed>
     */
    protected function getFieldAttributes($setting = []): array
    {
        if (empty($setting)) {
            return [];
        }

        return [
            'name'       => $setting['var_name'],
            'label'      => $setting['phrase'],
            'options'    => $setting['options'],
            'required'   => false,
            'validation' => [
                'positive' => true,
                'required' => true,
            ],
        ];
    }

    /**
     * @return array<mixed>
     */
    protected function getFieldEnableConditions(): array
    {
        return [
            'includes',
            'feed:view_wall',
            [
                MetaFoxPrivacy::FRIENDS,
                MetaFoxPrivacy::EVERYONE,
            ],
        ];
    }

    /**
     * @param  array<string,mixed> $setting
     * @return array<FormField>
     */
    protected function buildFields(array $setting = []): array
    {
        if (empty($setting['var_name'])) {
            return [];
        }

        switch ($setting['var_name']) {
            case 'event:manage_pending_post':
                return $this->buildPendingPostSettingField($setting);
            case 'feed:share_on_wall':
                return $this->buildShareSettingField($setting);
            default:
                return $this->buildSettingField($setting);
        }
    }

    /**
     * @param  array<string,mixed> $setting
     * @return array<FormField>
     */
    protected function buildSettingField(array $setting = []): array
    {
        return [
            new Dropdown($this->getFieldAttributes($setting)),
        ];
    }

    /**
     * @param  array<string,mixed> $setting
     * @return array<FormField>
     */
    protected function buildShareSettingField(array $setting = []): array
    {
        return [
            new Dropdown(array_merge($this->getFieldAttributes($setting), [
                'enabledWhen' => $this->getFieldEnableConditions(),
                'disabled'    => !$this->user->hasPermissionTo('event.discussion'),
            ])),
        ];
    }

    /**
     * @param  array<string,mixed> $setting
     * @return array<FormField>
     */
    protected function buildPendingPostSettingField(array $setting = []): array
    {
        return [
            Builder::switch('pending_mode')
                ->label(__p('event::phrase.can_approve_post'))
                ->labelPlacement('start')
                ->fullWidth(true)
                ->enableWhen($this->getFieldEnableConditions()),
        ];
    }
}
