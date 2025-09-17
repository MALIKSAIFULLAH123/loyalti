<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Form\Html;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Collection;
use MetaFox\Form\AbstractField;
use MetaFox\Form\Constants as MetaFoxForm;

/**
 * Class CategoryField.
 */
class CategoryField extends AbstractField
{
    /** @var mixed|null */
    protected ?string $repository               = null;
    private null|Collection $selectedCategories = null;

    public function initialize(): void
    {
        $this->component(MetaFoxForm::COMPONENT_SELECT)
            ->variant('outlined')
            ->fullWidth()
            ->label(__p('core::phrase.categories'))
            ->name('categories')
            ->valueType('array')
            ->multiple(true);
    }

    /**
     * @return mixed
     */
    public function getRepository(): mixed
    {
        return $this->repository;
    }

    /**
     * @param  mixed         $repository
     * @return CategoryField
     */
    public function setRepository(mixed $repository): self
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
