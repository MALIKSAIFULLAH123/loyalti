<?php

namespace MetaFox\Form\Mobile;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Contracts\Entity;

class CategoryField extends ChoiceField
{
    private ?string $repository                 = null;
    private null|Collection $selectedCategories = null;

    public function initialize(): void
    {
        $this->setComponent(MetaFoxForm::COMPONENT_SELECT)
            ->variant('standard')
            ->label(__p('core::phrase.categories'))
            ->placeholder(__p('core::phrase.select'))
            ->name('categories')
            ->valueType('array')
            ->multiple();
    }

    /**
     * @param  array<int, mixed> $options
     * @return $this
     */
    public function options(array $options): self
    {
        return $this->setAttribute('options', $options);
    }

    /**
     * @param  array<int, mixed> $subOptions
     * @return $this
     */
    public function subOptions(array $subOptions): self
    {
        return $this->setAttribute('subOptions', $subOptions);
    }

    /**
     * @param  string        $repository
     * @return CategoryField
     */
    public function setRepository(string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return null|Collection
     */
    public function getSelectedCategories(): null|Collection
    {
        return $this->selectedCategories;
    }

    /**
     * @param  null|Collection $selectedCategories
     * @return $this
     */
    public function setSelectedCategories(null|Collection $selectedCategories): self
    {
        $this->selectedCategories = $selectedCategories;

        return $this;
    }

    /**
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $options = $this->getAttribute('options');
        if (null !== $options) {
            return;
        }

        if (null === $this->repository) {
            return;
        }

        $this->setAttribute('options', $this->getOptions());
    }

    private function getOptions(): array
    {
        $repository         = resolve($this->repository);
        $selectedCategories = $this->getSelectedCategories();

        if (empty($selectedCategories)) {
            return $repository->getCategoriesForForm();
        }

        return $repository->getCategoriesForUpdateForm($selectedCategories);
    }
}
