<?php

namespace MetaFox\Event\Http\Resources\v1\Event;

use MetaFox\Event\Models\Event as Model;
use MetaFox\Event\Policies\EventPolicy;
use MetaFox\Event\Repositories\EventRepositoryInterface;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Form\Mobile\MobileForm as AbstractForm;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * Class SettingForm.
 * @property Model             $resource
 * @property array<int, mixed> $settings the list of settings
 */
class SettingMobileForm extends AbstractForm
{
    /**
     * @var array<int, mixed>
     */
    private array $settings;

    public function boot(
        EventRepositoryInterface $repository,
        UserPrivacyRepositoryInterface $privacyRepository,
        ?int $id = null
    ): void {
        $context        = user();
        $this->resource = $repository->find($id);
        policy_authorize(EventPolicy::class, 'update', $context, $this->resource);

        $this->settings = $privacyRepository->getProfileSettings($id);
    }

    protected function prepare(): void
    {
        $value = collect($this->settings)->pluck('value', 'var_name')->toArray();

        $this
            ->title(__('event::phrase.event_settings'))
            ->action('event/setting/' . $this->id)
            ->asPut()
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
     * @param  array<string,mixed>  $setting
     * @return array<AbstractField>
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
     * @param  array<string,mixed>  $setting
     * @return array<AbstractField>
     */
    protected function buildSettingField(array $setting = []): array
    {
        return [
            Builder::choice($setting['var_name'])
                ->label($setting['phrase'])
                ->options($setting['options'])
                ->disableClearable()
                ->enableSearch(false)
                ->required(false)
                ->yup(
                    Yup::number()->required()->int()
                ),
        ];
    }

    /**
     * @param  array<string,mixed>  $setting
     * @return array<AbstractField>
     */
    protected function buildShareSettingField(array $setting = []): array
    {
        return [
            Builder::choice($setting['var_name'])
                ->label($setting['phrase'])
                ->options($setting['options'])
                ->required(false)
                ->disableClearable()
                ->enableSearch(false)
                ->enableWhen($this->getFieldEnableConditions())
                ->yup(
                    Yup::number()->required()->int()
                ),
        ];
    }

    /**
     * @param  array<string,mixed>  $setting
     * @return array<AbstractField>
     */
    protected function buildPendingPostSettingField(array $setting = []): array
    {
        return [
            Builder::switch('pending_mode')
                ->label(__p('event::phrase.can_approve_post'))
                ->labelPlacement('start')
                ->disableClearable()
                ->fullWidth(true)
                ->enableWhen($this->getFieldEnableConditions()),
        ];
    }
}
