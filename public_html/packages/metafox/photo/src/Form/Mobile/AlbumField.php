<?php

namespace MetaFox\Photo\Form\Mobile;

use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Form\Mobile\ChoiceField;
use MetaFox\Photo\Repositories\Contracts\MediaAlbumRepositoryInterface;
use MetaFox\Platform\Contracts\User;

/**
 * class AlbumField.
 *
 * @driverType form-field-mobile
 * @driverName album
 */
class AlbumField extends ChoiceField
{
    private ?string $repository = null;

    /**
     * @var User|null
     */
    protected ?User $owner = null;

    /**
     * @var User|null
     */
    protected ?User $user = null;

    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::COMPONENT_SELECT)
            ->variant('standard')
            ->fullWidth()
            ->name('album')
            ->label(__p('core::phrase.album'))
            ->marginNormal();
    }

    /**
     * @return mixed|null
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param  string $repository
     * @return $this
     */
    public function setRepository(string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param  User|null  $owner
     * @return AlbumField
     */
    public function setOwner(User $owner = null): self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param  User|null  $user
     * @return AlbumField
     */
    public function setUser(User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $this->owner = $this->owner ?? $this->user;
        $options     = $this->getAttribute('options');
        if (null == $options && null != $this->repository) {
            /** @var mixed $repository */
            $repository = resolve($this->repository);
            if ($repository instanceof MediaAlbumRepositoryInterface) {
                $options = $repository->getAlbumsForForm($this->user, $this->owner);
                $this->setAttribute('options', $options);
            }
        }
    }
}
