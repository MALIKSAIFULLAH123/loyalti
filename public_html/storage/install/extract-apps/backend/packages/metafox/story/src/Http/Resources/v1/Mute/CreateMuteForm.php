<?php

namespace MetaFox\Story\Http\Resources\v1\Mute;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder as Builder;
use MetaFox\Platform\Contracts\User;
use MetaFox\Story\Http\Requests\v1\Mute\CreateRequest;
use MetaFox\Story\Models\Mute as Model;
use MetaFox\Story\Support\Facades\StoryFacades;
use MetaFox\Story\Support\StorySupport;
use MetaFox\User\Support\Facades\UserEntity;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub
 */

/**
 * Class CreateMuteForm
 *
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class CreateMuteForm extends AbstractForm
{
    protected ?User $user = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function boot(CreateRequest $request): void
    {
        $params = $request->all();
        $userId = Arr::get($params, 'user_id');

        $user = UserEntity::getById($userId)->detail;

        $this->setUser($user);
    }

    protected function prepare(): void
    {
        $this->title(__p('story::web.mute_user_stories', ['user_name' => $this->getUser()?->full_name]))
            ->action(apiUrl('story-mute.store'))
            ->asPost()
            ->setValue([
                'time'    => StorySupport::MUTED_FOREVER,
                'user_id' => $this->getUser()?->entityId(),
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::radioGroup('time')
                    ->required()
                    ->label(__p('story::phrase.how_long_do_you_want_to_muted_user_name_label', ['user_name' => $this->getUser()?->full_name]))
                    ->options(StoryFacades::getMutedOptions())
                    ->yup(Yup::string()->required()),
                Builder::hidden('user_id'),
            );

        $this->addFooter()
            ->addFields(
                Builder::submit()->label(__p('story::phrase.mute')),
                Builder::cancelButton(),
            );
    }
}
