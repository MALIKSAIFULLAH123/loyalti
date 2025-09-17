<?php

namespace MetaFox\Platform\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use MetaFox\Platform\Repositories\Contracts\CategoryRepositoryInterface;

/**
 * Class CategoryRule.
 */
class CategoryRule implements Rule
{
    protected bool                        $isExist = true;
    protected CategoryRepositoryInterface $repository;
    protected null|int                    $itemId  = null;

    public function __construct(CategoryRepositoryInterface $repository, ?int $itemId = null)
    {
        $this->repository = $repository;
        $this->itemId     = $itemId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function passes($attribute, $value): bool
    {
        $category = null;

        try {
            // fixed security risk: sql injection
            $category = $this->repository->find(intval($value, 10));
        } catch (ModelNotFoundException) {
        }

        if ($category == null) {
            $this->isExist = false;

            return false;
        }

        if ($this->isActiveCategory($category)) {
            return true;
        }

        return $this->hasLinkedItem($value);
    }

    private function isActiveCategory(Model $category): bool
    {
        if (!$category->is_active) {
            return false;
        }

        $activeCategoryIds = $this->repository->getActiveCategoryIds();

        return in_array($category->id, $activeCategoryIds);
    }

    private function hasLinkedItem(int $categoryId): bool
    {
        if (null === $this->itemId) {
            return false;
        }

        return $this->repository->hasLinkedItem($categoryId, $this->itemId);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        if (!$this->isExist) {
            return __p('core::validation.category_id.exists');
        }

        return __p('core::validation.category_id.active');
    }

    public function docs(): array
    {
        return [
            'type'   => 'integer',
            'setter' => (fn () => rand(1, 99)),
        ];
    }
}
