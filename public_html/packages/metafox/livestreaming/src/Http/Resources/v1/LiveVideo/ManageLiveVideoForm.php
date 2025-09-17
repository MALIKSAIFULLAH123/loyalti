<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\CreateFormRequest;
use MetaFox\LiveStreaming\Models\LiveVideo as Model;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Support\Traits\RepoTrait;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class StoreLiveVideoForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class ManageLiveVideoForm extends AbstractForm
{
    use RepoTrait;
    /**
     * @throws AuthorizationException
     * @throws AuthenticationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function boot(CreateFormRequest $request, LiveVideoRepositoryInterface $repository, ?int $id = null): void
    {
        $context        = user();
        $this->resource = $repository->find($id);
        policy_authorize(LiveVideoPolicy::class, 'manageLiveVideo', $context, $this->resource);
    }

    /**
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
        $text          = '';

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
        $this->title(__p('livestreaming::phrase.information'))
            ->action(url_utility()->makeApiResourceUrl('live-video', $this->resource->entityId()))
            ->secondAction('livestreaming/live-updated')
            ->asPut()
            ->setValue([
                'title'      => $this->resource->title,
                'text'       => $text,
                'privacy'    => $privacy,
                'owner_id'   => $this->resource->owner_id,
                'module_id'  => $this->resource->entityType(),
                'stream_key' => $this->resource->stream_key,
                'server_url' => $this->getServerUrl(),
            ]);
    }

    /**
     * @throws AuthenticationException
     */
    protected function initialize(): void
    {
        $basic   = $this->addBasic();
        $canEdit = policy_check(LiveVideoPolicy::class, 'update', user(), $this->resource);
        $title   = Builder::text('title')
            ->label(__p('core::phrase.title'))
            ->placeholder(__p('livestreaming::phrase.title_optional'))
            ->maxLength(255);
        $text = Builder::textArea('text')
            ->returnKeyType('default')
            ->setAttribute('enableEmoji', true)
            ->label(__p('core::phrase.description'))
            ->placeholder(__p('livestreaming::phrase.add_description_about_your_live_video'));
        if (!$canEdit) {
            $title->disabled();
            $text->disabled();
        }
        $basic->addFields(
            $title,
            $text,
            Builder::hidden('module_id')
                ->setValue('livestreaming'),
            Builder::hidden('owner_id'),
            // Temporary hide below fields because we haven't support persistent key yet
//            Builder::copyText('stream_key')
//                    ->label(__p('livestreaming::phrase.stream_key'))
//                    ->readOnly(),
//            Builder::copyText('server_url')
//                ->label(__p('livestreaming::phrase.server_url'))
//                ->readOnly()
        );
        $submitLabel = __p('livestreaming::phrase.update');
        $footer      = $this->addFooter();
        if ($canEdit) {
            $footer->addField(Builder::submit('__submit')->label($submitLabel));
        }
        $footer->addFields(
            Builder::customButton('end_live')
                ->sizeMedium()
                ->variant('contained')
                ->color('error')
                ->label(__p('livestreaming::phrase.end_live'))->customAction([
                    'type'    => 'livestreaming/end-live',
                    'payload' => [
                        'id' => $this->resource->entityId(),
                    ],
                ])
        );
    }

    protected function getServerUrl(): string
    {
        $service = $this->getServiceManager();

        return $service->getDefaultServiceProvider(true)->getLiveServerUrl();
    }
}
