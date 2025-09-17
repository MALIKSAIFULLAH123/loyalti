<?php

namespace MetaFox\FFMPEG\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Core\Rules\FileExistRule;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
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
        $module = 'ffmpeg';
        $vars   = [
            'ffmpeg.binaries',
            'ffmpeg.ffprobe_binaries',
            'ffmpeg.timeout',
        ];

        $value = [];
        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this->title(__p('ffmpeg::phrase.ffmpeg_configurations'))
            ->asPost()
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $minTimeout = 300;

        $this->addBasic()->addFields(
            Builder::text('ffmpeg.binaries')
                ->required()
                ->label(__p('ffmpeg::phrase.path_to_ffmpeg'))
                ->description(__p('ffmpeg::phrase.path_to_ffmpeg_description'))
                ->yup(Yup::string()->required()),
            Builder::text('ffmpeg.ffprobe_binaries')
                ->required()
                ->label(__p('ffmpeg::phrase.ffmpeg_path_to_ffprobe'))
                ->description(__p('ffmpeg::phrase.ffmpeg_path_to_ffprobe_description'))
                ->yup(Yup::string()->required()),
            Builder::text('ffmpeg.timeout')
                ->required()
                ->label(__p('ffmpeg::phrase.ffmpeg_timeout'))
                ->description(__p('ffmpeg::phrase.ffmpeg_timeout_description'))
                ->yup(
                    Yup::number()
                    ->required()
                    ->unint(__p('core::validation.integer', ['attribute' => '${path}']))
                    ->min($minTimeout, __p('ffmpeg::phrase.attribute_minimum_of_second', ['attribute' => '${path}', 'min' => $minTimeout]))
                    ->setError('typeError', __p('core::validation.integer', ['attribute' => '${path}']))
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
            'ffmpeg.binaries'         => ['sometimes', new FileExistRule()],
            'ffmpeg.ffprobe_binaries' => ['sometimes', new FileExistRule()],
        ];

        $validator = Validator::make($data, $rules);
        $validator->validate();

        return $data;
    }
}
