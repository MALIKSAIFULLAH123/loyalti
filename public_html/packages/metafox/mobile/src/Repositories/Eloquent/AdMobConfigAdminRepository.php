<?php

namespace MetaFox\Mobile\Repositories\Eloquent;

use Illuminate\Support\Arr;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Mobile\Repositories\AdMobConfigAdminRepositoryInterface;
use MetaFox\Mobile\Models\AdMobConfig as Model;
use MetaFox\Mobile\Repositories\AdMobPageAdminRepositoryInterface;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Traits\Helpers\InputCleanerTrait;

/**
 * Class AdMobConfigAdminRepository.
 *
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 */
class AdMobConfigAdminRepository extends AbstractRepository implements AdMobConfigAdminRepositoryInterface
{
    use InputCleanerTrait;

    public function model()
    {
        return Model::class;
    }

    /**
     * @param User                 $context
     * @param array<string, mixed> $attributes
     */
    public function createConfig(User $context, array $attributes = []): Model
    {
        $name  = Arr::get($attributes, 'name');
        $type  = Arr::get($attributes, 'type');
        $pages = Arr::get($attributes, 'pages', []);

        $this->canAddConfigToPage($pages, $type);

        $configParams = array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'name'      => $this->cleanTitle($name),
        ]);
        $config = new Model();
        $config->fill($configParams);
        $config->save();

        $config->pages()->syncWithPivotValues($pages, ['config_type' => $type]);
        $config->refresh();

        return $config;
    }

    /**
     * @param  array<int> $pages
     * @param  string     $type
     * @return void
     */
    protected function canAddConfigToPage(array $pages, string $type): void
    {
        foreach ($pages as $page) {
            if (!$this->getAdMobPageAdminRepository()->canAddConfigToPage($page, $type)) {
                abort(403, __p('mobile::phrase.one_of_your_page_already_has_this_type'));
            }
        }
    }

    /**
     * @param  array<int> $pages
     * @param  string     $type
     * @return void
     */
    protected function canUpdateConfigToPage(array $pages, Model $config): void
    {
        foreach ($pages as $page) {
            if (!$this->getAdMobPageAdminRepository()->canUpdateConfigToPage($page, $config->entityId())) {
                abort(403, __p('mobile::phrase.one_of_your_page_already_has_this_type'));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function updateConfig(User $context, int $id, array $attributes = []): Model
    {
        $name  = Arr::get($attributes, 'name');
        $type  = Arr::get($attributes, 'type');
        $pages = Arr::get($attributes, 'pages', []);
        $roles = Arr::get($attributes, 'roles', []);

        $config = $this->with(['roles', 'pages'])->find($id);
        if ($type !== $config->type) {
            $this->canUpdateConfigToPage($pages, $config);
        }

        $configParams = array_merge($attributes, [
            'user_id'   => $context->entityId(),
            'user_type' => $context->entityType(),
            'name'      => $this->cleanTitle($name),
        ]);

        $config->fill($configParams);
        $config->save();

        if (!empty($roles)) {
            $config->roles()->sync($roles);
        }

        if (!empty($pages)) {
            $config->pages()->syncWithPivotValues($pages, ['config_type' => $type]);
        }

        $config->refresh();

        return $config;
    }

    /**
     * @inheritDoc
     */
    public function deleteConfig(User $context, int $id): bool
    {
        $config = $this->find($id);

        $deleted = $config->delete();

        if ($deleted) {
            $config->roles()->detach();
            $config->pages()->detach();
        }

        return $deleted ?? false;
    }

    public function getAdMobPageAdminRepository(): AdMobPageAdminRepositoryInterface
    {
        return resolve(AdMobPageAdminRepositoryInterface::class);
    }
}
