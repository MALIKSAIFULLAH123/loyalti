<?php

namespace MetaFox\Video\Listeners;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Video\Models\Video;
use MetaFox\Video\Repositories\VideoRepositoryInterface;

/**
 * Class FeedComposerEditListener.
 * @ignore
 * @codeCoverageIgnore
 */
class FeedComposerEditListener
{
    /** @var VideoRepositoryInterface */
    private VideoRepositoryInterface $repository;

    /**
     * FeedComposerEditListener constructor.
     *
     * @param VideoRepositoryInterface $repository
     */
    public function __construct(VideoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param  User                 $user
     * @param  User                 $owner
     * @param  mixed                $item
     * @param  array<string, mixed> $params
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(User $user, User $owner, mixed $item, array $params): ?array
    {
        if ($item?->entityType() != Video::ENTITY_TYPE) {
            return null;
        }

        if (!$item instanceof Video) {
            throw new ModelNotFoundException();
        }

        $privacy      = Arr::get($params, 'privacy', $item->privacy);
        $locationName = Arr::get($params, 'location_name');
        $locationLat  = Arr::get($params, 'location_latitude');
        $locationLong = Arr::get($params, 'location_longitude');
        $content      = Arr::get($params, 'content');

        if (null === $content) {
            $content = MetaFoxConstant::EMPTY_STRING;
        }

        $videoParams = [
            'privacy'            => $privacy,
            'content'            => $params['content'],
            'text'               => $content,
            'location_name'      => $locationName,
            'location_latitude'  => $locationLat,
            'location_longitude' => $locationLong,
            'location_address'   => Arr::get($params, 'location_address'),
        ];

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $item->loadPrivacyListAttribute();

            Arr::set($videoParams, 'list', Arr::get($params, 'list', $item->getPrivacyListAttribute()));
        }

        if (is_array($image = Arr::pull($params, 'thumbnail'))) {
            $videoParams['thumb_temp_file']  = Arr::get($image, 'id', 0);

            $videoParams['remove_thumbnail'] = Arr::get($image, 'status') === MetaFoxConstant::FILE_REMOVE_STATUS;
        }

        $this->repository->updatePatchVideo($item->entityId(), $videoParams);

        $oldPhrase = null;

        if (Arr::get($params, 'is_first_history')) {
            $oldPhrase = 'video::phrase.added_video';
        }

        return [
            'success' => true,
            'phrase'  => [
                'old' => $oldPhrase,
                'new' => null,
            ],
            'extra' => [
                'old' => [],
                'new' => [],
            ],
        ];
    }
}
