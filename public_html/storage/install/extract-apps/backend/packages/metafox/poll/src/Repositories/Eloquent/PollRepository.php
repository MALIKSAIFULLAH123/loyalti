<?php

namespace MetaFox\Poll\Repositories\Eloquent;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use MetaFox\Core\Repositories\AttachmentRepositoryInterface;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\HasApprove as HasApproveContract;
use MetaFox\Platform\Contracts\HasFeature;
use MetaFox\Platform\Contracts\HasPrivacyMember;
use MetaFox\Platform\Contracts\HasSponsor as HasSponsorContract;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\Exceptions\PrivacyException;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\ResourcePermission;
use MetaFox\Platform\Support\Browse\Browse;
use MetaFox\Platform\Support\Browse\Scopes\FeaturedScope;
use MetaFox\Platform\Support\Browse\Scopes\PrivacyScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Browse\Scopes\TagScope;
use MetaFox\Platform\Support\Browse\Scopes\WhenScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasFeatured;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\Platform\Traits\Helpers\InputCleanerTrait;
use MetaFox\Poll\Models\Answer;
use MetaFox\Poll\Models\Poll;
use MetaFox\Poll\Policies\PollPolicy;
use MetaFox\Poll\Repositories\PollRepositoryInterface;
use MetaFox\Poll\Repositories\ResultRepositoryInterface;
use MetaFox\Poll\Support\Browse\Scopes\Poll\SortScope;
use MetaFox\Poll\Support\Browse\Scopes\Poll\ViewScope;
use MetaFox\Poll\Support\Facade\Poll as PollFacade;
use MetaFox\User\Traits\UserMorphTrait;

/**
 * Class PollRepository.
 * @property Poll $model
 * @method   Poll getModel()
 * @method   Poll find($id, $columns = ['*'])
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class PollRepository extends AbstractRepository implements PollRepositoryInterface
{
    use HasApprove;
    use HasFeatured;
    use HasSponsor;
    use HasSponsorInFeed;
    use InputCleanerTrait;
    use CollectTotalItemStatTrait;
    use UserMorphTrait;

    public function model(): string
    {
        return Poll::class;
    }

    public function viewPolls(User $context, User $owner, array $attributes): Paginator
    {
        $view      = $attributes['view'];
        $profileId = $attributes['user_id'];
        $limit     = $attributes['limit'];

        $this->withUserMorphTypeActiveScope();

        if ($view == Browse::VIEW_FEATURE) {
            return $this->findFeature($limit); //@todo: implement setting feature limit value
        }

        if ($context->entityId() && $profileId == $context->entityId() && $view != Browse::VIEW_PENDING) {
            $attributes['view'] = $view = Browse::VIEW_MY;
        }

        if (Browse::VIEW_PENDING == $view) {
            if (Arr::get($attributes, 'user_id') == 0) {
                if ($context->isGuest() || !$context->hasPermissionTo('poll.approve')) {
                    throw new AuthorizationException(__p('core::validation.this_action_is_unauthorized'), 403);
                }
            }
        }

        $query = $this->buildQueryViewPolls($context, $owner, $attributes);

        if (!in_array($view, [Browse::VIEW_PENDING, Browse::VIEW_MY_PENDING])) {
            //In case polls are belongs to Forum Thread...
            $query->where('polls.view_id', '!=', PollFacade::getIntegrationViewId());
        }

        $relations = [
            'pollText',
            'userEntity',
            'user',
            'answers' => fn (HasMany $query) => $query->orderBy('ordering'),
        ];

        return $query
            ->with($relations)
            ->simplePaginate($limit, ['polls.*']);
    }

    /**
     * @param User                 $context
     * @param User                 $owner
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildQueryViewPolls(User $context, User $owner, array $attributes): Builder
    {
        $sort       = $attributes['sort'];
        $sortType   = $attributes['sort_type'];
        $when       = $attributes['when'];
        $view       = $attributes['view'];
        $search     = $attributes['q'];
        $searchTag  = $attributes['tag'] ?? '';
        $profileId  = $attributes['user_id'];
        $isFeatured = Arr::get($attributes, 'is_featured');

        // Scopes.
        $privacyScope = new PrivacyScope();
        $privacyScope
            ->setUserId($context->entityId())
            ->setModerationPermissionName('poll.moderate')
            ->setHasUserBlock(true);

        $sortScope = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $whenScope = new WhenScope();
        $whenScope->setWhen($when);

        $viewScope = new ViewScope();
        $viewScope->setUserContext($context)->setView($view)->setProfileId($profileId);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['question']));
        }

        $query->addScope(new FeaturedScope($isFeatured));

        if ($searchTag != '') {
            $query = $query->addScope(new TagScope($searchTag));
        }

        if ($owner->entityId() != $context->entityId()) {
            $privacyScope->setOwnerId($owner->entityId());

            $viewScope->setIsViewOwner(true);
            if (!$context->can('approve', [Poll::class, resolve(Poll::class)])) {
                $query->where('polls.is_approved', '=', Poll::IS_APPROVED);
            }
        }

        $query = $this->applyDisplaySetting($query, $owner, $view);

        if (!$isFeatured) {
            $query->addScope($privacyScope);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($whenScope)
            ->addScope($viewScope);
    }

    /**
     * @param  Builder $query
     * @param  User    $owner
     * @param  string  $view
     * @return Builder
     */
    private function applyDisplaySetting(Builder $query, User $owner, string $view): Builder
    {
        if ($view == Browse::VIEW_MY) {
            return $query;
        }

        /*
         * Does not support view pending items from Group in My Pending Polls
         */
        if (!$owner instanceof HasPrivacyMember) {
            $query->where('polls.owner_type', '=', $owner->entityType());
        }

        return $query;
    }

    public function viewPoll(User $context, int $id): Poll
    {
        $relations = [
            'design',
            'pollText',
            'attachments',
            'answers' => fn (HasMany $query) => $query->orderBy('ordering'),
        ];
        $poll = $this->withUserMorphTypeActiveScope()->with($relations)->find($id);

        policy_authorize(PollPolicy::class, 'view', $context, $poll);

        $poll->incrementAmount('total_view');
        $poll->refresh();

        return $poll;
    }

    /**
     * @throws AuthorizationException | Exception
     */
    public function createPoll(User $context, User $owner, array $attributes): Poll
    {
        $policyMethod = 'create';

        if (isset($attributes['policy_method'])) {
            $policyMethod = $attributes['policy_method'];
        }

        policy_authorize(PollPolicy::class, $policyMethod, $context, $owner);

        $this->checkUploadImagePermission($context, $attributes);

        $attributes = array_merge($attributes, [
            'user_id'    => $context->entityId(),
            'user_type'  => $context->entityType(),
            'owner_id'   => $owner->entityId(),
            'owner_type' => $owner->entityType(),
        ]);

        $attributes = $this->cleanData($attributes);

        $forceApproved      = Arr::get($attributes, 'force_approved', false);
        $prohibitedApproval = Arr::get($attributes, 'prohibited_approval', false);

        if (!$forceApproved) {
            $attributes['is_approved'] = policy_check(PollPolicy::class, 'autoApprove', $context, $owner);
        }

        if ($owner->hasPendingMode()) {
            $attributes['is_approved'] = 1;
        }

        if ($prohibitedApproval) {
            $attributes['is_approved'] = 0;
        }

        if (!empty($attributes['temp_file'])) {
            $tempFile                    = upload()->getFile($attributes['temp_file']);
            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        /** @var Poll $poll */
        $poll = $this->getModel()->newModelInstance();
        $poll->fill($attributes);

        if ($attributes['privacy'] == MetaFoxPrivacy::CUSTOM) {
            $poll->setPrivacyListAttribute($attributes['list']);
        }

        $poll->save();

        $this->createPollAnswer($poll, $attributes['answers']);

        if (!empty($attributes['attachments'])) {
            resolve(AttachmentRepositoryInterface::class)->updateItemId($attributes['attachments'], $poll);
        }

        return $this
            ->withUserMorphTypeActiveScope()
            ->with(['answers', 'design', 'pollText', 'attachments'])
            ->find($poll->entityId());
    }

    /**
     * @param Poll                 $poll
     * @param array<string, mixed> $answers
     */
    private function createPollAnswer(Poll $poll, array $answers): void
    {
        $newAnswers = [];
        foreach ($answers as $answer) {
            $newAnswers[] = new Answer([
                'answer'   => $this->cleanTitle($answer['answer']),
                'ordering' => $answer['ordering'],
            ]);
        }
        $poll->answers()->saveMany($newAnswers);
    }

    /**
     * @throws AuthorizationException | Exception
     */
    public function updatePoll(User $context, int $id, array $attributes): Poll
    {
        /** @var Poll $poll */
        $poll = $this
            ->withUserMorphTypeActiveScope()
            ->getModel()
            ->with([
                'answers', 'design', 'pollText', 'attachments',
            ])
            ->find($id);

        policy_authorize(PollPolicy::class, 'update', $context, $poll);

        if (Arr::has($attributes, 'privacy') && !$context->can('updatePrivacy', [$poll, $attributes['privacy']])) {
            throw new PrivacyException(403, __p('core::phrase.the_current_item_is_either_sponsored_or_featured'));
        }

        $this->checkUploadImagePermission($context, $attributes);

        $attributes = $this->cleanData($attributes);

        if (!empty($attributes['remove_image'])) {
            app('storage')->rollDown($poll->image_file_id);
            $attributes['image_file_id'] = null;
        }

        if (!empty($attributes['temp_file'])) {
            $tempFile                    = upload()->getFile($attributes['temp_file']);
            $attributes['image_file_id'] = $tempFile->id;

            // Delete temp file after done
            upload()->rollUp($attributes['temp_file']);
        }

        $poll->fill($attributes);

        if (Arr::get($attributes, 'privacy') == MetaFoxPrivacy::CUSTOM) {
            $poll->setPrivacyListAttribute($attributes['list']);
        }

        $poll->save();

        if (isset($attributes['answers']['changedAnswers'])) {
            $this->updatePollAnswer($poll, $attributes['answers']['changedAnswers']);
        }

        if (isset($attributes['answers']['newAnswers'])) {
            $this->createPollAnswer($poll, $attributes['answers']['newAnswers']);
        }

        if (isset($attributes['attachments'])) {
            resolve(AttachmentRepositoryInterface::class)->updateItemId($attributes['attachments'], $poll);
        }

        $poll->refresh();

        $this->updateFeedStatus($poll);

        app('events')->dispatch('poll.repositories.override_update_poll', [$poll, $attributes]);

        return $poll;
    }

    protected function updateFeedStatus(Poll $poll): void
    {
        app('events')->dispatch('activity.feed.mark_as_pending', [$poll]);
    }

    private function checkUploadImagePermission(User $user, array $attributes): void
    {
        if (!array_key_exists('file', $attributes) || $attributes['file'] === null) {
            return;
        }

        policy_authorize(PollPolicy::class, 'uploadImage', $user);
    }

    /**
     * @param Poll                 $poll
     * @param array<string, mixed> $answers
     *
     * @return Poll
     */
    protected function updatePollAnswer(Poll $poll, array $answers): Poll
    {
        $cachedAnswerIds = [];

        // Update old answers
        foreach ($poll->answers as $currentAnswer) {
            if (!array_key_exists($currentAnswer->entityId(), $answers)) {
                continue;
            }

            // All the current answer ids not in this array shall be deleted
            $cachedAnswerIds[] = $currentAnswer->entityId();

            //Update answer info
            $answerData = $answers[$currentAnswer->entityId()];
            $currentAnswer->fill([
                'answer'   => $this->cleanTitle($answerData['answer']),
                'ordering' => $answerData['ordering'],
            ]);
            $currentAnswer->save();
        }

        // Delete ids which are not included
        $poll->answers->except($cachedAnswerIds)->each(function (Answer $item) {
            $item->delete();
        });

        resolve(ResultRepositoryInterface::class)->updateAnswersPercentage($poll);

        return $poll;
    }

    /**
     * @throws AuthorizationException
     */
    public function deletePoll(User $context, int $id): int
    {
        $poll = $this->withUserMorphTypeActiveScope()->find($id);

        policy_authorize(PollPolicy::class, 'delete', $context, $poll);

        return $this->delete($id);
    }

    public function findFeature(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('view_id', '!=', PollFacade::getIntegrationViewId())
            ->where('is_featured', HasFeature::IS_FEATURED)
            ->where('is_approved', HasApproveContract::IS_APPROVED)
            ->orderByDesc(HasFeature::FEATURED_AT_COLUMN)
            ->simplePaginate($limit);
    }

    public function findSponsor(int $limit = 4): Paginator
    {
        return $this->getModel()->newQuery()
            ->where('view_id', '!=', PollFacade::getIntegrationViewId())
            ->where('is_sponsor', HasSponsorContract::IS_SPONSOR)
            ->where('is_approved', HasApproveContract::IS_APPROVED)
            ->simplePaginate($limit);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    protected function cleanData(array $attributes): array
    {
        if (isset($attributes['question'])) {
            $attributes['question'] = $this->cleanTitle($attributes['question']);
        }

        if (isset($attributes['background'])) {
            $attributes['background'] = $this->cleanTitle($attributes['background']);
        }

        if (isset($attributes['percentage'])) {
            $attributes['percentage'] = $this->cleanTitle($attributes['percentage']);
        }

        if (isset($attributes['border'])) {
            $attributes['border'] = $this->cleanTitle($attributes['border']);
        }

        if (isset($attributes['caption'])) {
            $attributes['caption'] = $this->cleanContent($attributes['caption']);
        }

        return $attributes;
    }

    /**
     * @param User $context
     * @param int  $id
     *
     * @return Poll
     * @throws AuthorizationException
     */
    public function getPollByAnswerId(User $context, int $id): Poll
    {
        $poll = $this->getModel()->newQuery()->whereHas('answers', function (Builder $q) use ($id) {
            $q->where('id', $id);
        })->first();

        if (!$poll instanceof Poll) {
            throw (new ModelNotFoundException())->setModel(Poll::class);
        }

        policy_authorize(PollPolicy::class, 'view', $context, $poll);

        return $poll;
    }

    public function isUserVoted(User $context, int $id): bool
    {
        $poll = $this->withUserMorphTypeActiveScope()->with(['results'])->find($id);

        $votedUserIds = $poll->results->pluck('user_id')->toArray();

        return in_array($context->entityId(), $votedUserIds);
    }

    /**
     * @param  User          $context
     * @param  int           $id
     * @param  string|null   $attachedPermissionName
     * @return array|mixed[]
     */
    public function getDataForEditIntegration(User $context, int $id, ?string $attachedPermissionName = null): array
    {
        $item = $this->find($id);

        $policy = PolicyGate::getPolicyFor(Poll::class);

        $currentAnswers = $item->answers->map(function (Answer $answer) {
            return [
                'answer' => $answer->answer,
                'id'     => $answer->entityId(),
                'order'  => $answer->ordering,
            ];
        });

        $allowClose = (int) (null != $item->closed_at);

        $isPublicVote = $item->public_vote ? 1 : 0;

        $allowMultiple = $item->is_multiple ? 1 : 0;

        $canDelete = $policy->delete($context, $item);

        if (null !== $attachedPermissionName) {
            $canDelete = $canDelete && $context->hasPermissionTo($attachedPermissionName);
        }

        $data = [
            'poll_question'   => $item->question,
            'poll_answers'    => $currentAnswers,
            'poll_close_time' => $item->closed_at,
            'poll_public'     => $isPublicVote,
            'enable_close'    => $allowClose,
            'poll_multiple'   => $allowMultiple,
            'permissions'     => [
                ResourcePermission::CAN_EDIT   => $policy->update($context, $item),
                ResourcePermission::CAN_DELETE => $canDelete,
            ],
        ];

        return $data;
    }

    /**
     * @param  User                      $context
     * @param  int                       $id
     * @return array<string, mixed>|null
     * @throws AuthorizationException
     */
    public function copy(User $context, int $id): ?array
    {
        $poll = $this->withUserMorphTypeActiveScope()->with([
            'answers' => fn (HasMany $query) => $query->orderBy('ordering'),
        ])->find($id);

        $owner = $poll->owner;

        if (!policy_check(PollPolicy::class, 'copy', $context)) {
            return null;
        }

        if (!$poll->isApproved()) {
            return null;
        }

        $data = $poll->toArray();

        $data = array_merge($data, [
            'answers'        => [],
            'is_approve'     => 1,
            'force_approved' => true,
        ]);

        $answers = $poll->answers;

        if (null !== $answers) {
            foreach ($answers as $key => $answer) {
                $data['answers'][$key] = [
                    'answer'   => $answer->answer,
                    'ordering' => $key++,
                ];
            }
        }

        $data['policy_method'] = 'copy';
        $data['total_vote']    = 0;

        $new = $this->createPoll($context, $owner, $data);

        if (null === $new) {
            return null;
        }

        return [
            'item_id'   => $new->id,
            'item_type' => $new->entityType(),
        ];
    }

    public function prepareDataForFeed(array $attributes): array
    {
        $attributes = array_merge($attributes, [
            'content'     => '',
            'question'    => $attributes['poll_question'] ?? '',
            'answers'     => $attributes['poll_answers'] ?? '',
            'public_vote' => $attributes['poll_public'] ?? '',
            'is_multiple' => $attributes['poll_multiple'] ?? '',
        ]);

        if (Arr::has($attributes, 'user_status') && null !== $attributes['user_status']) {
            Arr::set($attributes, 'content', Arr::get($attributes, 'user_status'));
        }

        $enableClose = Arr::has($attributes, 'enable_close') && Arr::get($attributes, 'enable_close') == 1;

        $closedTime = $enableClose && Arr::has($attributes, 'poll_close_time') ? Arr::get(
            $attributes,
            'poll_close_time'
        ) : null;

        $attributes = array_merge($attributes, [
            'enable_close' => $enableClose ? 1 : 0,
            'closed_at'    => $closedTime,
        ]);

        if (is_array($attributes['answers'])) {
            $ordering = 0;
            foreach ($attributes['answers'] as $key => $answer) {
                $answer['ordering']          = ++$ordering;
                $attributes['answers'][$key] = $answer;
            }
        }

        unset($attributes['user_status']);
        unset($attributes['poll_question']);
        unset($attributes['poll_answers']);
        unset($attributes['poll_close_time']);
        unset($attributes['poll_public']);
        unset($attributes['poll_multiple']);

        return $attributes;
    }
}
