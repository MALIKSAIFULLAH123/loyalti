<?php

namespace MetaFox\Page\Observers;

use MetaFox\Page\Jobs\DeleteActivitiesJob;
use MetaFox\Page\Models\Page as Model;
use MetaFox\Page\Repositories\IntegratedModuleRepositoryInterface;
use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Page\Repositories\PageMemberRepositoryInterface;
use MetaFox\User\Support\Facades\UserEntity;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class PageObserver.
 */
class PageObserver
{
    public function __construct(protected PageCategoryRepositoryInterface $categoryRepository) {}

    /**
     * @throws ValidatorException
     */
    public function created(Model $model): void
    {
        $service = resolve(PageMemberRepositoryInterface::class);

        $service->addPageAdmin($model, $model->userId());

        $this->updateUserEntity($model);
        resolve(IntegratedModuleRepositoryInterface::class)->addModules($model->entityId());

        $category = $model->category;
        do {
            $category?->incrementTotalItem();
            $category = $category?->parentCategory;
        } while ($category);
    }

    public function updated(Model $model): void
    {
        $this->updateUserEntity($model);
        $this->updateCategoryCount($model);
    }

    public function deleted(Model $model): void
    {
        $model->pageText()->delete();
        $model->pageClaim()->delete();
        $model->invites()->delete();
        $model->members()->delete();

        DeleteActivitiesJob::dispatch([
            'page_id' => $model->entityId(),
        ]);

        $category = $model->category;
        do {
            $category?->decrementTotalItem();
            $category = $category?->parentCategory;
        } while ($category);
    }

    private function updateUserEntity(Model $model): void
    {
        $update = [];

        if ($model->isDirty(['avatar_id', 'avatar_file_id'])) {
            $update = array_merge($update, [
                'avatar_file_id' => $model->avatar_file_id,
                'avatar_id'      => $model->getAvatarId(),
                'avatar_type'    => $model->getAvatarType(),
            ]);
        }

        if (count($update)) {
            UserEntity::updateEntity($model->entityId(), $update);
        }
    }

    private function updateCategoryCount(Model $model): void
    {
        if (!$model->isDirty('category_id')) {
            return;
        }

        $oldCategoryId = $model->getOriginal('category_id');
        if ($oldCategoryId) {
            $this->categoryRepository->find($oldCategoryId)?->decrementTotalItem();
        }

        $category = $model->category;
        do {
            $category?->incrementTotalItem();
            $category = $category?->parentCategory;
        } while ($category);
    }
}
