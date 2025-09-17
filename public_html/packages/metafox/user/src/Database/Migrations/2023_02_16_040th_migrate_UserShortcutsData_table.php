<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\User\Models\User;
use MetaFox\User\Repositories\UserShortcutRepositoryInterface;

/*
 * stub: /packages/database/migration.stub
 */

/*
 * @ignore
 * @codeCoverageIgnore
 * @link \$PACKAGE_NAMESPACE$\Models
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $shortcutTypes = app('events')->dispatch('user.get_shortcut_type');
        $query         = DB::table('core_privacy_members as member')
            ->join('core_privacy as privacy', function (JoinClause $join) {
                $join->on('member.privacy_id', '=', 'privacy.privacy_id');
                $join->where('privacy.item_type', '!=', User::ENTITY_TYPE);
                $join->where('privacy.item_type', '!=', MetaFoxPrivacy::PRIVACY_NETWORK_ITEM_TYPE);
            })
            ->join('user_entities', function (JoinClause $join) {
                $join->on('user_entities.id', '=', 'privacy.item_id');
            })
            ->whereIn('user_entities.entity_type', Arr::wrap($shortcutTypes))
            ->get(['member.user_id', 'privacy.user_type', 'privacy.item_type', 'privacy.item_id']);

        foreach ($query as $data) {
            $data = collect($data)->toArray();

            if ($data['user_id'] == $data['item_id']) {
                continue;
            }

            resolve(UserShortcutRepositoryInterface::class)
                ->getModel()
                ->newModelQuery()
                ->firstOrCreate($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //
    }
};
