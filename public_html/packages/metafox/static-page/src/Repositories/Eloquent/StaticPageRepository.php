<?php

namespace MetaFox\StaticPage\Repositories\Eloquent;

use Illuminate\Support\Collection;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\StaticPage\Models\StaticPage;
use MetaFox\StaticPage\Repositories\StaticPageContentRepositoryInterface;
use MetaFox\StaticPage\Repositories\StaticPageRepositoryInterface;

/**
 * stub: /packages/repositories/eloquent_repository.stub.
 */

/**
 * Class StaticPageRepository.
 */
class StaticPageRepository extends AbstractRepository implements StaticPageRepositoryInterface
{
    public function model()
    {
        return StaticPage::class;
    }

    public function createStaticPage(array $attributes): StaticPage
    {
        $staticPage = $this->getModel()->newModelInstance();
        $staticPage->fill($attributes);
        $staticPage->save();
        $staticPage->refresh();

        $this->getContentRepository()->updateOrCreateContent($staticPage, $attributes);

        $staticPage->loadMissing(['contents', 'masterContent', 'content']);

        return $staticPage;
    }

    public function updateStaticPage(int $id, array $attributes): StaticPage
    {
        $staticPage = $this->find($id);

        $staticPage->fill($attributes);

        $staticPage->save();
        $staticPage->refresh();

        $this->getContentRepository()->updateOrCreateContent($staticPage, $attributes);

        return $staticPage;
    }

    public function deleteStaticPage(int $id): bool
    {
        return $this->delete($id);
    }

    public function getAllStaticPage(): Collection
    {
        return $this->getModel()->newQuery()->get();
    }

    public function getStaticPageOptions(): array
    {
        $staticPages = $this->getAllStaticPage()->toArray();

        return array_map(function ($page) {
            return [
                'label' => $page['title'],
                'value' => $page['id'],
            ];
        }, $staticPages);
    }

    public function getStaticPageUrlById(?int $id): string
    {
        $page = $this->getModel()->newModelQuery()->find($id);

        return $page instanceof StaticPage ? $page->toUrl() : '';
    }

    protected function getContentRepository(): StaticPageContentRepositoryInterface
    {
        return resolve(StaticPageContentRepositoryInterface::class);
    }
}
