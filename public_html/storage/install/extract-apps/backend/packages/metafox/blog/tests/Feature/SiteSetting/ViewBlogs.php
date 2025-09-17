<?php

namespace MetaFox\Blog\Tests\Feature\SiteSetting;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Blog\Models\Blog as Model;
use MetaFox\Blog\Repositories\BlogRepositoryInterface;
use MetaFox\Blog\Repositories\Eloquent\BlogRepository;
use MetaFox\Blog\Support\Browse\Scopes\Blog\ViewScope;
use MetaFox\Group\Models\Group;
use MetaFox\Page\Models\Page;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Browse\Scopes\SortScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\UserRole;
use Tests\TestCase;

/**
 * Class BlogRepositoryActionViewBlogs.
 */
class ViewBlogs extends TestCase
{
    public function testInstance(): array
    {
        if (!app_active('metafox/page')) {
            $this->markTestSkipped('Not applicable!');
        }

        $user       = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        $page       = Page::factory()->setUser($user)->create();
        $repository = resolve(BlogRepositoryInterface::class);
        $this->assertInstanceOf(BlogRepository::class, $repository);

        Model::factory()->setUser($user)->setOwner($page)->create(['privacy' => 0]);
        Model::factory()->setUser($user)->setOwner($user)->create(['privacy' => 0]);

        return [
            $user, $repository, [
                'q'           => '',
                'tag'         => '',
                'sort'        => SortScope::SORT_DEFAULT,
                'sort_type'   => SortScope::SORT_TYPE_DEFAULT,
                'when'        => WhenScope::WHEN_DEFAULT,
                'view'        => ViewScope::VIEW_DEFAULT,
                'limit'       => 1,
                'category_id' => 0,
                'user_id'     => 0,
            ],
        ];
    }

    /**
     * @depends testInstance
     * @param  array                         $data
     * @return array<int,             mixed>
     * @throws AuthorizationException
     */
    public function testTurnOffSettingViewBlogWithViewMy(array $data): array
    {
        /**
         * @var User                           $user
         * @var BlogRepositoryInterface        $repository
         * @var array<string,           mixed> $params
         */
        [$user, $repository, $params] = $data;

        $page  = Page::factory()->setUser($user)->create();
        $group = Group::factory()->setUser($user)->create();

        $blogInPage  = Model::factory()->setUser($user)->setOwner($page)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $blogInGroup = Model::factory()->setUser($user)->setOwner($group)->create(['privacy' => MetaFoxPrivacy::EVERYONE]);

        $params['view']  = 'my';
        $params['limit'] = 5;

        $results = $repository->viewBlogs($user, $user, $params);
        $ids     = collect($results->items())->pluck('id')->toArray();
        $this->assertInArray($blogInPage->entityId(), $ids);
        $this->assertInArray($blogInGroup->entityId(), $ids);

        return [$user, $repository, $params, [$blogInPage, $blogInGroup]];
    }

    /**
     * @depends testTurnOffSettingViewBlogWithViewMy
     * @throws AuthorizationException
     */
    public function testTurnOffSettingViewBlogWithSearch(array $data): array
    {
        /**
         * @var User                           $user
         * @var BlogRepositoryInterface        $repository
         * @var array<string,           mixed> $params
         * @var Model                          $blogInPage
         * @var Model                          $blogInGroup
         */
        [$user, $repository, $params, $items] = $data;
        [$blogInPage, $blogInGroup]           = $items;

        $params['view'] = 'all';
        $params['q']    = $blogInPage->title;

        $results = $repository->viewBlogs($user, $user, $params);
        $ids     = collect($results->items())->pluck('id')->toArray();
        $this->assertInArray($blogInPage->entityId(), $ids);

        $params['view'] = 'all';
        $params['q']    = $blogInGroup->title;

        $results = $repository->viewBlogs($user, $user, $params);
        $ids     = collect($results->items())->pluck('id')->toArray();
        $this->assertInArray($blogInGroup->entityId(), $ids);

        return $data;
    }
}
