<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\LiveStreaming\Repositories\StreamingServiceRepositoryInterface;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Yup\Yup;

/**
 * | --------------------------------------------------------------------------
 * | Form Configuration
 * | --------------------------------------------------------------------------
 * | stub: src/Http/Resources/v1/Admin/SiteSettingForm.stub.
 */

/**
 * Class SiteSettingForm.
 * @codeCoverageIgnore
 * @ignore
 */
class SiteSettingForm extends AbstractForm
{
    protected function prepare(): void
    {
        $module = 'livestreaming';
        $vars   = [
            'livestreaming.custom_video_playback_url',
            'livestreaming.custom_thumbnail_playback_url',
            'livestreaming.streaming_service',
            'livestreaming.filter_streaming_content_by_minutes',
            'livestreaming.display_live_section_on_mobile',
            'livestreaming.allow_webcam_streaming',
            'livestreaming.webcam_websocket_url',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('core::phrase.settings'))
            ->asPost()
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $basic = $this->addBasic();
        $basic->addFields(
            Builder::choice('livestreaming.streaming_service')
                ->required()
                ->label(__p('livestreaming::phrase.streaming_service'))
                ->description(__p('livestreaming::phrase.streaming_service_description'))
                ->multiple(false)
                ->options($this->getServiceOptions()),
            Builder::text('livestreaming.custom_video_playback_url')
                ->label(__p('livestreaming::phrase.custom_video_playback_url'))
                ->description(__p('livestreaming::phrase.custom_video_playback_url_description'))
                ->yup(
                    Yup::string()
                        ->format('url')
                        ->setError(__p('chatplus::validation.custom_video_playback_url_invalid'))
                ),
            Builder::text('livestreaming.custom_thumbnail_playback_url')
                ->label(__p('livestreaming::phrase.custom_thumbnail_playback_url'))
                ->description(__p('livestreaming::phrase.custom_thumbnail_playback_url_description'))
                ->yup(
                    Yup::string()
                        ->format('url')
                        ->setError(__p('livestreaming::validation.custom_thumbnail_playback_url_invalid'))
                ),
            Builder::text('livestreaming.filter_streaming_content_by_minutes')
                ->label(__p('livestreaming::phrase.filter_streaming_content_by_minutes'))
                ->description(__p('livestreaming::phrase.filter_streaming_content_by_minutes_description'))
                ->yup(
                    Yup::number()
                        ->int()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::switch('livestreaming.display_live_section_on_mobile')
                ->label(__p('livestreaming::phrase.display_live_section_on_mobile')),
            Builder::switch('livestreaming.allow_webcam_streaming')
                ->description(__p('livestreaming::phrase.allow_webcam_guide', [
                    'link' => 'https://docs.phpfox.com/display/MFMAN/Live+Streaming+-+Set+up+transcoder+server+for+live+videos+with+webcam',
                ]))
                ->label(__p('livestreaming::phrase.allow_webcam_streaming')),
            Builder::text('livestreaming.webcam_websocket_url')
                ->label(__p('livestreaming::phrase.transcoder_server_url'))
                ->description(__p('livestreaming::phrase.transcoder_server_url_description'))
                ->showWhen(['truthy', 'livestreaming.allow_webcam_streaming'])
                ->requiredWhen(['truthy', 'livestreaming.allow_webcam_streaming'])
                ->yup(
                    Yup::string()
                        ->when(
                            Yup::when('allow_webcam_streaming')
                                ->is(true)
                                ->then(
                                    Yup::string()
                                        ->required()
                                        ->format('url')
                                        ->setError('required', __p('validation.this_field_is_a_required_field'))
                                )
                        )
                ),
        );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request              $request
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $data  = $request->all();
        $rules = [
            'livestreaming.streaming_service' => ['required'],
        ];

        $validator = Validator::make($data, $rules);
        $validator->validate();

        return $data;
    }

    /**
     * @return array<int, mixed>
     */
    protected function getServiceOptions(): array
    {
        return resolve(StreamingServiceRepositoryInterface::class)->getServicesOptions();
    }
}
