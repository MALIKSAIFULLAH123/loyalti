<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\LiveVideo;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use MetaFox\Form\AbstractField;
use MetaFox\LiveStreaming\Policies\LiveVideoPolicy;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\LiveStreaming\Http\Requests\v1\LiveVideo\CreateFormRequest;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\MetaFoxPrivacy;
use MetaFox\Platform\Support\Facades\PrivacyPolicy;
use MetaFox\Form\Mobile\Builder;
use MetaFox\Yup\Yup;

class UpdateLiveVideoMobileForm extends StoreLiveVideoMobileForm
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
                'title'     => $this->resource->title,
                'text'      => $text,
                'privacy'   => $privacy,
                'owner_id'  => $this->resource->owner_id,
                'module_id' => 'livestreaming',
            ]);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();

        $basic->addFields(
            Builder::text('title')
                ->label(__p('core::phrase.title'))
                ->placeholder(__p('livestreaming::phrase.title_optional'))
                ->maxLength(255),
            Builder::singlePhoto('file')
                ->required(false)
                ->itemType('photo')
                ->accept('image/*')
                ->acceptFail(__p('photo::phrase.photo_accept_type_fail'))
                ->label(__p('livestreaming::phrase.video_thumbnail'))
                ->previewUrl($this->resource->image),
            $this->buildTextField(),
            Builder::hidden('module_id')
                ->setValue('livestreaming'),
            Builder::hidden('owner_id'),
        );

        // Handle build privacy field with custom criteria
        $basic->addField(
            $this->buildPrivacyField()
                ->description(__p('livestreaming::phrase.control_who_can_see_this_live_video'))
        );
    }

    protected function buildTextField(): AbstractField
    {
        $settingAllowHtml = Settings::get('core.general.allow_html', true);

        if ($settingAllowHtml) {
            return Builder::richTextEditor('text')
                ->returnKeyType('default')
                ->label(__p('core::phrase.description'))
                ->asMultiLine()
                ->placeholder(__p('livestreaming::phrase.add_description_about_your_live_video'));
        }

        return Builder::textArea('text')
            ->returnKeyType('default')
            ->label(__p('core::phrase.description'))
            ->asMultiLine()
            ->placeholder(__p('livestreaming::phrase.add_description_about_your_live_video'));
    }
}
