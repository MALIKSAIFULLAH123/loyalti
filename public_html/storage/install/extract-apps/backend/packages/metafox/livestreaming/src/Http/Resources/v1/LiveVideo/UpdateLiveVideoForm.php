<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\CreateFormRequest;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\User\Http\Resources\v1\UserEntity\UserEntityCollection;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateLiveVideoForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateLiveVideoForm extends StoreLiveVideoForm
{
    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, LiveVideoRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        $this->setOwner($this->resource->owner);

        policy_authorize(LiveVideoPolicy::class, 'update', $context, $this->resource);
    }

    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    protected function prepare(): void
    {
        $privacy = $this->resource->privacy;

        if ($privacy == MetaFoxPrivacy::CUSTOM) {
            $lists = PrivacyPolicy::getPrivacyItem($this->resource);

            $listIds = [];
            if (!empty($lists)) {
                $listIds = array_column($lists, 'item_id');
            }

            $privacy = $listIds;
        }

        $liveVideoText = $this->resource->liveVideoText;

        $text = '';

        if (null !== $liveVideoText) {
            if (MetaFoxConstant::EMPTY_STRING != $liveVideoText->text_parsed) {
                $text = $liveVideoText->text_parsed;
            }
        }

        if (MetaFoxConstant::EMPTY_STRING == $text) {
            if ($this->resource->group_id > 0) {
                $reactItem = $this->resource->reactItem();

                if (null !== $reactItem) {
                    $text = $reactItem->content;
                }
            }
        }

        $this->title(__p('livestreaming::phrase.edit_live_video'))
            ->action(url_utility()->makeApiResourceUrl('live-video', $this->resource->entityId()))
            ->setBackProps(__p('core::web.video'))
            ->asPut()
            ->setValue([
                'title'          => $this->resource->title,
                'text'           => $text,
                'privacy'        => $privacy,
                'owner_id'       => $this->resource->owner_id,
                'module_id'      => 'livestreaming',
                'location'       => $this->resource->toLocationObject(),
                'tagged_friends' => new UserEntityCollection($this->getLiveVideoRepository()->getTaggedFriends(user(), $this->resource)),
            ]);
    }
}
