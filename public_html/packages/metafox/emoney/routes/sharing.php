<?php
use Illuminate\Support\Facades\Route;
use MetaFox\SEO\SeoMetaData;

Route::get('ewallet/user-balance/{id}/adjustment-history/browse', function (int $id) {
    return seo_sharing_view(
        'admin',
        'admin.ewallet.browse_adjustment_history',
        null,
        null,
        function (SeoMetaData $data, $resource) use ($id) {
            $data->addBreadcrumb(__p('ewallet::admin.user_balances'), '/ewallet/user-balance/browse');

            /**
             * @var \MetaFox\User\Models\User $user
             */
            $user = resolve(\MetaFox\User\Repositories\Contracts\UserRepositoryInterface::class)->find($id);

            $data->addBreadcrumb($user->toTitle());
        }
    );
});
