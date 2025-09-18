<?php

namespace MetaFox\Quiz\Tests\Unit\Models;

use MetaFox\Core\Contracts\HasTotalAttachment;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasGlobalSearch;
use MetaFox\Platform\Contracts\HasPrivacy;
use MetaFox\Platform\Contracts\HasResourceStream;
use MetaFox\Platform\Contracts\HasSavedItem;
use MetaFox\Platform\Contracts\HasSponsor;
use MetaFox\Platform\Contracts\HasSponsorInFeed;
use MetaFox\Platform\Contracts\HasThumbnail;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\UserRole;
use MetaFox\Quiz\Models\Quiz;
use Tests\TestCase;

class QuizTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return Quiz
     */
    public function testCreateQuiz(): Quiz
    {
        $user = $this->createUser()->assignRole(UserRole::NORMAL_USER);
        /** @var Quiz $model */
        $model = Quiz::factory()
            ->setUser($user)
            ->setOwner($user)
            ->create(['privacy' => MetaFoxPrivacy::EVERYONE]);
        $model->refresh();

        $this->assertNotEmpty($model->owner->entityId());
        $this->assertNotEmpty($model->owner->entityType());
        $this->assertNotEmpty($model->user->entityId());
        $this->assertNotEmpty($model->user->entityType());

        $this->assertInstanceOf(Content::class, $model);
        $this->assertInstanceOf(ActivityFeedSource::class, $model);
        $this->assertInstanceOf(FeedAction::class, $model->toActivityFeed());

        $this->assertInstanceOf(AppendPrivacyList::class, $model);
        $this->assertInstanceOf(HasPrivacy::class, $model);

        $this->assertInstanceOf(HasResourceStream::class, $model);

        $this->assertInstanceOf(HasApprove::class, $model);
        $this->assertInstanceOf(HasFeature::class, $model);
        $this->assertInstanceOf(HasSponsor::class, $model);
        $this->assertInstanceOf(HasSponsorInFeed::class, $model);

        $this->assertInstanceOf(HasTotalLike::class, $model);
        $this->assertInstanceOf(HasTotalShare::class, $model);
        $this->assertInstanceOf(HasTotalCommentWithReply::class, $model);
        $this->assertInstanceOf(HasTotalView::class, $model);
        $this->assertInstanceOf(HasTotalAttachment::class, $model);

        // $this->assertInstanceOf(HasLocationCheckin::class, $model);
        $this->assertInstanceOf(HasThumbnail::class, $model);
        $this->assertInstanceOf(HasSavedItem::class, $model);
        // $this->assertInstanceOf(HasHashTag::class, $model);
        // $this->assertInstanceOf(HasTaggedFriend::class, $model);
        $this->assertInstanceOf(HasGlobalSearch::class, $model);

        return $model;
    }
}
