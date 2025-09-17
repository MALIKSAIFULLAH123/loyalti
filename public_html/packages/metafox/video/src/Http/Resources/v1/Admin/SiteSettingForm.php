<?php

namespace MetaFox\Video\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AdminSettingForm as Form;
use MetaFox\Form\Builder;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Rules\AllowInRule;
use MetaFox\Video\Contracts\ProviderManagerInterface;
use MetaFox\Video\Models\Video as Model;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class SiteSettingForm.
 * @property ?Model $resource
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'video';
        $vars   = [
            'video.video_service',
            'video.minimum_name_length',
            'video.maximum_name_length',
            'video.default_category',
            'video.minimal_play_time_threshold',
            'video.enable_video_uploads',
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
        $maximumNameLength = MetaFoxConstant::DEFAULT_MAX_TITLE_LENGTH;
        $basic             = $this->addBasic();

        $basic->addFields(
            Builder::alert('__warning_message')
                ->asWarning()
                ->showWhen([
                    'and',
                    ['truthy', 'video.enable_video_uploads'],
                    ['includes', 'video.video_service', $this->getServiceNotReady()],
                ])
                ->message(__p('video::phrase.video_service_dont_ready_warning_message')),
            Builder::switch('video.enable_video_uploads')
                ->label(__p('video::phrase.enable_video_uploads'))
                ->description(__p('video::phrase.enable_video_uploads_description')),
            Builder::choice('video.video_service')
                ->required()
                ->label(__p('video::phrase.video_service_to_process_video'))
                ->description(__p('video::phrase.video_service_to_process_video_description'))
                ->multiple(false)
                ->options($this->getServiceOptions()),
        );

        $categories = $this->getCategoryRepository()->getCategoriesForForm();

        $basic->addFields(
            Builder::text('video.minimum_name_length')
                ->label(__p('video::phrase.minimum_name_length'))
                ->yup(
                    Yup::number()->required()->int()->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::text('video.maximum_name_length')
                ->label(__p('video::phrase.maximum_name_length'))
                ->maxLength($maximumNameLength)
                ->yup(
                    Yup::number()->required()->int()
                        ->when(
                            Yup::when('minimum_name_length')
                                ->is('$exists')
                                ->then(
                                    Yup::number()
                                        ->min(['ref' => 'minimum_name_length'])
                                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                                )
                        )
                        ->max($maximumNameLength)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
            Builder::choice('video.default_category')
                ->label(__p('video::phrase.video_default_category'))
                ->description(__p('video::phrase.video_default_category_description'))
                ->disableClearable()
                ->required()
                ->options($categories),
            Builder::text('video.minimal_play_time_threshold')
                ->label(__p('video::phrase.minimal_play_time_threshold'))
                ->description(__p('video::phrase.minimal_play_time_threshold_description'))
                ->yup(
                    Yup::number()
                        ->required()
                        ->int()
                        ->min(1)
                        ->setError('typeError', __p('core::validation.numeric', ['attribute' => '${path}']))
                ),
        );

        $this->addDefaultFooter(true);
    }

    public function getCategoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }

    /**
     * @param Request $request
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validated(Request $request): array
    {
        $data  = $request->all();
        $rules = [
            'video.video_service' => ['required', new AllowInRule(array_values(Arr::pluck($this->getServiceOptions(), 'value')))],
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
        return $this->providerRepository()->getServicesOptions();
    }

    protected function checkReadyService(string $driver): bool
    {
        try {
            $service = $this->providerRepository()->getVideoServiceClassByDriver($driver);

            if (method_exists($service, 'testConfig')) {
                return $service->testConfig();
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getServiceNotReady(): array
    {
        $value          = [];
        $serviceOptions = Arr::pluck($this->getServiceOptions(), 'value');
        foreach ($serviceOptions as $option) {
            if ($this->checkReadyService($option)) {
                continue;
            }

            $value[] = $option;
            break;
        }
        return $value;
    }

    protected function providerRepository(): ProviderManagerInterface
    {
        return resolve(ProviderManagerInterface::class);
    }
}
