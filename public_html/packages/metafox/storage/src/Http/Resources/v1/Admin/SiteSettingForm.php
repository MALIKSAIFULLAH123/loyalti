<?php

namespace MetaFox\Storage\Http\Resources\v1\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use MetaFox\Form\AdminSettingForm as Form;
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
 */
class SiteSettingForm extends Form
{
    protected function prepare(): void
    {
        $module = 'storage';
        $vars   = [
            'storage.filesystems.default',
            'storage.filesystems.max_upload_filesize',
        ];

        $value = [];

        foreach ($vars as $var) {
            Arr::set($value, $var, Settings::get($var));
        }

        $this
            ->title(__p('core::phrase.settings'))
            ->action(url_utility()->makeApiUrl('admincp/setting/' . $module))
            ->asPost()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $serverSizeLimit = $this->getServerSizeLimit();

        $this->addBasic()
            ->addFields(
                Builder::selectStorageId('storage.filesystems.default')
                    ->label(__p('storage::phrase.select_default_storage_label'))
                    ->description(__p('storage::phrase.select_default_storage_desc'))
                    ->excludes(['local', 'asset', 'web'])
                    ->required()
                    ->yup(Yup::string()->required()),
                Builder::text('storage.filesystems.max_upload_filesize.photo')
                    ->label(__p('storage::phrase.photo_max_upload_size_title'))
                    ->description(__p('storage::phrase.photo_max_upload_size_description', [
                        'title'       => __p('storage::phrase.photo_max_upload_size_title'),
                        'serverLimit' => $serverSizeLimit,
                    ]))
                    ->asNumber()
                    ->preventScrolling()
                    ->yup(
                        Yup::number()
                            ->required()
                            ->unint()
                            ->min(0)
                            ->max($serverSizeLimit)
                    ),
                Builder::text('storage.filesystems.max_upload_filesize.video')
                    ->label(__p('storage::phrase.video_max_upload_size_title'))
                    ->description(__p('storage::phrase.video_max_upload_size_description', [
                        'title'       => __p('storage::phrase.video_max_upload_size_title'),
                        'serverLimit' => $serverSizeLimit,
                    ]))
                    ->asNumber()
                    ->preventScrolling()
                    ->yup(
                        Yup::number()
                            ->required()
                            ->unint()
                            ->min(0)
                            ->max($serverSizeLimit)
                    ),
                Builder::text('storage.filesystems.max_upload_filesize.music')
                    ->label(__p('storage::phrase.music_max_upload_size_title'))
                    ->description(__p('storage::phrase.music_max_upload_size_description', [
                        'title'       => __p('storage::phrase.music_max_upload_size_title'),
                        'serverLimit' => $serverSizeLimit,
                    ]))
                    ->asNumber()
                    ->preventScrolling()
                    ->yup(
                        Yup::number()
                            ->required()
                            ->unint()
                            ->min(0)
                            ->max($serverSizeLimit)
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
        $data = $request->all();

        $rules = [
            'storage.filesystems.max_upload_filesize.photo' => ['numeric', 'max:' . $this->getServerSizeLimit()],
            'storage.filesystems.max_upload_filesize.video' => ['numeric', 'max:' . $this->getServerSizeLimit()],
            'storage.filesystems.max_upload_filesize.music' => ['numeric', 'max:' . $this->getServerSizeLimit()],
        ];

        $validator = Validator::make(
            $data,
            $rules,
        );

        $validator->validate();

        $maxUploadFileSize = Arr::get($data, 'storage.filesystems.max_upload_filesize');

        Arr::set($data, 'storage.filesystems.max_upload_filesize', array_map(fn ($value) => (int) $value, $maxUploadFileSize));

        return $data;
    }

    protected function getServerSizeLimit(): int
    {
        $postMaxSize   = trim(ini_get('post_max_size'));
        $uploadMaxSize = trim(ini_get('upload_max_filesize'));
        $maxSize       = $postMaxSize;

        if (mb_substr($maxSize, 0, mb_strlen($maxSize) - 1) > mb_substr($uploadMaxSize, 0, mb_strlen($uploadMaxSize) - 1)) {
            $maxSize = $uploadMaxSize;
        }

        $value = mb_substr($maxSize, 0, mb_strlen($maxSize) - 1);
        $unit  = strtolower(mb_substr($maxSize, -1));

        return match ($unit) {
            'g' => round($value * 1024 * 1024 * 1024),
            'm' => round($value * 1024 * 1024),
            'k' => round($value * 1024),
        };
    }
}
