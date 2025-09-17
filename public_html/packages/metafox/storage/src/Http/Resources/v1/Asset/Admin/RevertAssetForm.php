<?php

namespace MetaFox\Storage\Http\Resources\v1\Asset\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Yup\Yup;
use MetaFox\Form\Builder;
use MetaFox\Storage\Models\Asset as Model;
use MetaFox\Storage\Models\StorageFile;
use MetaFox\Storage\Repositories\AssetRepositoryInterface;

/**
 * Class RevertAssetForm.
 * @property ?Model $resource
 * @ignore
 * @codeCoverageIgnore
 */
class RevertAssetForm extends AbstractForm
{
    private ?StorageFile $defaultAsset;

    public function boot(AssetRepositoryInterface $repository, int $id): void
    {
        $this->resource = $repository->find($id);

        $this->defaultAsset = $repository->getDefaultAssetFile($this->resource?->entityId());
    }

    protected function prepare(): void
    {
        $this->title(__p('core::phrase.reset_to_default'))
            ->action(apiUrl('admin.storage.asset.revert', ['asset' => $this->resource->entityId()]))
            ->asPut()
            ->setValue([
                'default_file_id' => $this->defaultAsset?->entityId() ?: 0,
            ]);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::typography('notice')
                    ->plainText(__p('storage::phrase.your_asset_be_reset_to_default')),
                Builder::restoreDefault('file')
                    ->from([
                        'dataSource' => [
                            'file_name' => basename($this->resource->url),
                            'file_type' => $this->resource->file_mime_type,
                            'url'       => $this->resource->url,
                        ],
                    ])
                    ->to($this->getDefaultFileData()),
                Builder::hidden('default_file_id'),
            );
        $this->addFooter()->addFields(
            Builder::submit()
                ->label(__p('core::phrase.submit'))
                ->confirmation([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('core::phrase.action_cant_be_undone'),
                ]),
            Builder::cancelButton(),
        );
    }

    /**
     * @return array
     */
    protected function getDefaultFileData(): array
    {
        if (!$this->defaultAsset instanceof StorageFile) {
            return [
                'dataSource' => [
                    'file_name' => basename($this->resource->url),
                    'file_type' => $this->resource->file_mime_type,
                    'url'       => '',
                ],
            ];
        }

        $url = $this->defaultAsset->url;

        return [
            'dataSource' => [
                'file_name' => basename($url),
                'file_type' => $this->defaultAsset->mime_type,
                'url'       => $url,
            ],
        ];
    }
}
