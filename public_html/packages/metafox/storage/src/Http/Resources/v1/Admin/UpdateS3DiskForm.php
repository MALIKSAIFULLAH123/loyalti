<?php

namespace MetaFox\Storage\Http\Resources\v1\Admin;

use Illuminate\Contracts\Filesystem\Filesystem;
use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateS3DiskRequest as Request;
use MetaFox\Storage\Support\StorageDiskValidator;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateS3Form.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateS3DiskForm extends AbstractForm
{
    private array $variables = [];

    protected function prepare(): void
    {
        $resource = $this->resource;
        $action   = apiUrl('admin.storage.option.update', ['driver' => $resource['driver'], 'disk' => $resource['id']]);
        $values   = $resource['value'] ?? [];
        $values   = array_merge(['visibility' => 'public', 'driver' => 's3'], $values);

        $this->title(__p('storage::phrase.aws_s3_edit_title'))
            ->action($action)
            ->asPut()
            ->setValue($values);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('key')
                    ->required()
                    ->label(__p('storage::phrase.aws_s3_access_key_id'))
                    ->yup(Yup::string()->required()),
                Builder::text('secret')
                    ->required()
                    ->label(__p('storage::phrase.aws_s3_secret_key'))
                    ->yup(Yup::string()->required()),
                Builder::text('bucket')
                    ->required()
                    ->label(__p('storage::phrase.aws_s3_bucket'))
                    ->yup(Yup::string()->required()),
                Builder::text('region')
                    ->required()
                    ->label(__p('storage::phrase.aws_s3_region'))
                    ->yup(Yup::string()->required()),
                Builder::text('url')
                    ->label(__p('storage::phrase.aws_s3_url'))
                    ->yup(Yup::string()->format('url')->nullable()),
                Builder::text('endpoint')
                    ->label(__p('storage::phrase.aws_s3_endpoint'))
                    ->yup(Yup::string()->format('url')),
                Builder::checkbox('use_path_style_endpoint')
                    ->label(__p('storage::phrase.aws_s3_use_path_style_endpoint'))
                    ->checkedValue(true)
                    ->uncheckedValue(false)
                    ->description('https://docs.aws.amazon.com/AmazonS3/latest/userguide/VirtualHosting.html#path-style-access'),
                Builder::checkbox('throw')
                    ->checkedValue(true)
                    ->uncheckedValue(false)
                    ->label(__p('storage::phrase.storage_throws')),
                Builder::hidden('driver'),
            );

        $this->addDefaultFooter(true);
    }

    public function validated(Request $request, string $driver)
    {
        $data               = $request->validated();
        $data['selectable'] = true;
        $data['label']      = sprintf('%s:%s.%s', $this->resource['id'], $data['bucket'], $data['region']);
        $data['options']    = [
            'visibility' => Filesystem::VISIBILITY_PUBLIC,
        ];

        StorageDiskValidator::isValid($data);

        return $data;
    }
}
