<?php

namespace MetaFox\Storage\Http\Resources\v1\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateFtpDiskRequest as Request;
use MetaFox\Storage\Support\SelectDiskVisibility;
use MetaFox\Storage\Support\StorageDiskValidator;
use MetaFox\Yup\Yup;
use RuntimeException;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateFtpForm.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateFtpDiskForm extends AbstractForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;
        $action   = apiUrl('admin.storage.option.update', ['driver' => $resource['driver'], 'disk' => $resource['id']]);
        $value    = $resource['value'] ?? [];
        $value    = array_merge([
            'visibility' => 'public',
            'driver'     => 'ftp',
            'port'       => null,
            'timeout'    => null,
            'throw'      => null,
            'passive'    => false,
            'ssl'        => false,
        ], $value);

        $this->title(__p('storage::phrase.update_fpt_title'))
            ->action($action)
            ->asPut()
            ->setValue($value);
    }

    protected function initialize(): void
    {
        $this->addBasic()
            ->addFields(
                Builder::text('host')
                    ->required()
                    ->label(__p('storage::phrase.ftp_host_name'))
                    ->yup(Yup::string()->required()),
                Builder::text('port')
                    ->label(__p('storage::phrase.ftp_port'))
                    ->description(__p('storage::phrase.ftp_port_desc'))
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->unint()
                            ->min(0)
                    ),
                Builder::text('timeout')
                    ->label(__p('storage::phrase.ftp_timeout'))
                    ->description(__p('storage::phrase.ftp_timeout_desc'))
                    ->yup(
                        Yup::number()
                            ->nullable()
                            ->unint()
                            ->min(5)
                    ),
                Builder::text('username')
                    ->required()
                    ->label(__p('storage::phrase.ftp_username'))
                    ->yup(Yup::string()->required()),
                Builder::text('password')
                    ->required()
                    ->label(__p('storage::phrase.ftp_password'))
                    ->yup(Yup::string()->required()),
                Builder::text('root')
                    ->required()
                    ->label(__p('storage::phrase.ftp_root'))
                    ->yup(Yup::string()->required()),
                Builder::text('url')
                    ->required()
                    ->label(__p('storage::phrase.ftp_base_url'))
                    ->yup(Yup::string()->required()),
                new SelectDiskVisibility(),
                Builder::checkbox('throw')
                    ->required()
                    ->label(__p('storage::phrase.storage_throws')),
                Builder::checkbox('passive')
                    ->label(__p('storage::phrase.ftp_passive_mode')),
                Builder::checkbox('ssl')
                    ->label(__p('storage::phrase.ftp_enable_ssl')),
                Builder::hidden('driver'),
            );

        $this->addDefaultFooter();
    }

    /**
     * @param  Request          $request
     * @return array
     * @throws RuntimeException
     */
    public function validated(Request $request, string $disk): array
    {
        $data = $request->validated();

        $data = array_merge($data, [
            'label' => sprintf('ftp%s', $disk ? ':' . $disk : ''),
        ]);

        StorageDiskValidator::isValid($data);

        return $data;
    }
}
