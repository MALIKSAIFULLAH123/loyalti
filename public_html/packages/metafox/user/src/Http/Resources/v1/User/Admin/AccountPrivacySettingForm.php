<?php

namespace MetaFox\User\Http\Resources\v1\User\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\GenderTrait;
use MetaFox\Form\Html\Dropdown;
use MetaFox\User\Models\User as Model;
use MetaFox\User\Repositories\Contracts\UserRepositoryInterface;
use MetaFox\User\Repositories\UserPrivacyRepositoryInterface;

/**
 * Class AccountPrivacySettingForm.
 *
 * @property Model $resource
 * @driverType form
 * @driverName user.update.privacy
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class AccountPrivacySettingForm extends AbstractForm
{
    use GenderTrait;

    public const VAR_NAME_KEY = 'var_name';

    public function boot(int $id, UserRepositoryInterface $repository): void
    {
        $this->resource = $repository->find($id);
    }

    protected function prepare(): void
    {
        $values = [];

        $values['privacy'] = $this->getDefaultPrivacyValue();

        $this->title(__p('core::web.profile_privacy'))
            ->action('admincp/user/profile-privacy/' . $this->resource->id)
            ->resetFormOnSuccess(false)
            ->asPatch()
            ->setValue($values);
    }

    public function initialize(): void
    {
        $container = $this->addBasic();

        foreach ($this->getProfilePrivacy() as $setting) {
            $container->addField(
                new Dropdown([
                    'name'    => 'privacy.' . $setting['var_name'],
                    'label'   => $setting['phrase'],
                    'options' => $setting['options'],
                ])
            );
        }
        $this->addDefaultFooter(true);
    }

    private function getProfilePrivacy(): array
    {
        $user      = $this->resource;
        $privacies = resolve(UserPrivacyRepositoryInterface::class)
            ->getProfileSettings($user->entityId());

        $privacies[] = resolve(UserPrivacyRepositoryInterface::class)
            ->getBirthdaySetting($user);

        return $privacies;
    }

    private function getDefaultPrivacyValue(): array
    {
        $values = [];
        foreach ($this->getProfilePrivacy() as $privacy) {
            $values[$privacy[static::VAR_NAME_KEY]] = $privacy['value'];
        }

        return $values;
    }
}
