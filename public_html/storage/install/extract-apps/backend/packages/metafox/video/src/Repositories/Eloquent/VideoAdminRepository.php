<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Video\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use MetaFox\Core\Mails\Mailable;
use MetaFox\Core\Traits\CollectTotalItemStatTrait;
use MetaFox\Platform\Contracts\User as ContractUser;
use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Platform\Support\Browse\Scopes\CategoryScope;
use MetaFox\Platform\Support\Browse\Scopes\SearchScope;
use MetaFox\Platform\Support\Repository\HasApprove;
use MetaFox\Platform\Support\Repository\HasSponsor;
use MetaFox\Platform\Support\Repository\HasSponsorInFeed;
use MetaFox\User\Support\Browse\Scopes\User\SearchScope as UserSearchScope;
use MetaFox\Video\Models\VerifyProcess;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Video\Repositories\VideoAdminRepositoryInterface;
use MetaFox\Video\Repositories\VideoRepositoryInterface;
use MetaFox\Video\Support\Browse\Scopes\Video\SortScope;
use MetaFox\Video\Support\Browse\Scopes\Video\ViewAdminScope;
use MetaFox\Video\Support\VideoSupport;

/**
 * Class VideoRepository.
 * @method Model getModel()
 * @method Model find($id, $columns = ['*'])
 * @method Model newModelInstance()
 *
 * @property Model $model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class VideoAdminRepository extends AbstractRepository implements VideoAdminRepositoryInterface
{
    use HasSponsor;
    use HasApprove;
    use HasSponsorInFeed;
    use CollectTotalItemStatTrait;

    public function model(): string
    {
        return Model::class;
    }

    /**
     * @return CategoryRepositoryInterface
     */
    private function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @return VideoRepositoryInterface
     */
    private function videoRepository(): VideoRepositoryInterface
    {
        return resolve(VideoRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function viewVideos(ContractUser $context, array $attributes): Builder
    {
        $this->withUserMorphTypeActiveScope();

        $query = $this->buildQueryViewVideos($context, $attributes);

        $relations = ['videoText', 'user', 'userEntity', 'categories'];

        return $query->with($relations);
    }

    /**
     * @param ContractUser         $context
     * @param array<string, mixed> $attributes
     *
     * @return Builder
     */
    private function buildQueryViewVideos(ContractUser $context, array $attributes): Builder
    {
        $sort        = Arr::get($attributes, 'sort', SortScope::SORT_DEFAULT);
        $sortType    = Arr::get($attributes, 'sort_type', SortScope::SORT_TYPE_DEFAULT);
        $view        = Arr::get($attributes, 'view', ViewAdminScope::VIEW_DEFAULT);
        $search      = Arr::get($attributes, 'q');
        $categoryId  = Arr::get($attributes, 'category_id');
        $searchUser  = Arr::get($attributes, 'user_name');
        $searchOwner = Arr::get($attributes, 'owner_name');
        $createdFrom = Arr::get($attributes, 'created_from');
        $createdTo   = Arr::get($attributes, 'created_to');
        $isValid     = Arr::get($attributes, 'is_valid');
        $table       = $this->getModel()->getTable();
        $sortScope   = new SortScope();
        $sortScope->setSort($sort)->setSortType($sortType);

        $viewScope = new ViewAdminScope();
        $viewScope->setUserContext($context)->setView($view);

        $query = $this->getModel()->newQuery();

        if ($search != '') {
            $query = $query->addScope(new SearchScope($search, ['title']));
        }

        if ($categoryId > 0) {
            if (!is_array($categoryId)) {
                $categoryId = $this->categoryRepository()->getChildrenIds($categoryId);
            }

            $categoryScope = new CategoryScope();
            $categoryScope->setCategories($categoryId);
            $query = $query->addScope($categoryScope);
        }

        $searchScope = new UserSearchScope();
        $searchScope->setTable($table);

        if ($searchOwner) {
            $searchScope->setAliasJoinedTable('owner');
            $searchScope->setSearchText($searchOwner);
            $searchScope->setFieldJoined('owner_id');
            $query->addScope($searchScope);
        }

        if ($searchUser) {
            $searchScope->setAliasJoinedTable('user');
            $searchScope->setSearchText($searchUser);
            $searchScope->setFieldJoined('user_id');
            $query->addScope($searchScope);
        }

        if ($createdFrom) {
            $query->where("$table.created_at", '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where("$table.created_at", '<=', $createdTo);
        }

        if ($isValid !== null) {
            $query->where("$table.is_valid", $isValid);
        }

        return $query
            ->addScope($sortScope)
            ->addScope($viewScope);
    }

    /**
     * @inheritDoc
     */
    public function deleteVideo(ContractUser $context, int $id): bool
    {
        return $this->videoRepository()->deleteVideo($context, $id);
    }

    /**
     * Check video existence and update validity status
     *
     * @param Model $video The video model to check
     * @return void Returns true if video is valid, false otherwise
     */
    public function checkVideoExistence(?ContractUser $context, Model $video): void
    {
        try {
            $data = [];

            if ($video->video_url) {
                $data = app('events')->dispatch('core.process_parse_url', [$video->video_url, $context, []], true);
            }

            if ($video->video_file_id !== null && $video->video_file_id > 0) {
                $data = $video->destination;
            }

            if (!empty($data)) {
                $video->updateQuietly([
                    'is_valid'    => true,
                    'verified_at' => now(),
                ]);
                return;
            }

            // Video no longer exists or is invalid
            $video->updateQuietly([
                'is_valid'    => false,
                'verified_at' => now(),
            ]);

            return;
        } catch (\Exception $e) {
            $video->updateQuietly([
                'is_valid'    => false,
                'verified_at' => now(),
            ]);

            Log::error('Error checking video existence: ' . $e->getMessage(), [
                'video_id' => $video->entityId(),
            ]);
            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function sendMailDoneVerifyExistence(?ContractUser $context): void
    {
        $email = $context?->getEmailForVerification();

        if (!$email) {
            Log::warning('Context user is invalid or missing email.');
            return;
        }

        $url = url_utility()->makeApiFullUrl('admincp/video/video/browse?is_valid=0&view=all');

        try {
            $mailable = new Mailable();
            $mailable->subject(__p('video::phrase.verification_completed_subject'))
                ->line(__p('video::phrase.verification_completed_message'))
                ->action(__p('video::phrase.view_non_existent_video'), $url);

            Mail::to($email)->send($mailable);

        } catch (\Exception $e) {
            Log::error('Failed to send verification completion email: ' . $e->getMessage(), [
                'email' => $email,
            ]);
        }
    }

    public function handleSpecificVerification(array $videoIds, VerifyProcess $process): void
    {
        $videos = $this->getModel()->newQuery()->whereIn('id', $videoIds)->cursor();

        /**@var Collection<Video> $videos */
        foreach ($videos as $video) {
            $this->checkVideoExistence($process->user, $video);
            $process->updateQuietly([
                'total_verified' => $process->total_verified + 1,
                'last_id'        => $video->entityId(),
            ]);
        }

        $status = VideoSupport::PENDING_VERIFY_STATUS;
        $process->refresh();

        if ($process->total_verified == $process->total_videos) {
            $this->sendMailDoneVerifyExistence($process->user);
            $status = VideoSupport::COMPLETED_VERIFY_STATUS;
        }

        $process->updateQuietly([
            'status' => $status,
        ]);
    }
}
