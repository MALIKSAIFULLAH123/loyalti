<?php

use MetaFox\Platform\Support\DbTableHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        if (!Schema::hasTable('forum_post_quotes')) {
            return;
        }

        $quotes = \MetaFox\Forum\Models\ForumPostQuote::query()
            ->with(['quotedPost', 'quotedPost.postText'])
            ->join('forum_posts', function (\Illuminate\Database\Query\JoinClause $joinClause) {
                $joinClause->on('forum_post_quotes.quote_id', '=', 'forum_posts.id');
            })
            ->whereNull('forum_post_quotes.quote_content')
            ->get(['forum_post_quotes.*']);

        if (!$quotes->count()) {
            return;
        }

        $quotes->each(function (\MetaFox\Forum\Models\ForumPostQuote $forumPostQuote) {
            if (null === $forumPostQuote?->quotedPost?->postText?->text_parsed) {
                return;
            }

            $forumPostQuote->timestamps = false;

            $forumPostQuote->updateQuietly(['quote_content' => $forumPostQuote?->quotedPost?->postText?->text_parsed]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
    }
};
