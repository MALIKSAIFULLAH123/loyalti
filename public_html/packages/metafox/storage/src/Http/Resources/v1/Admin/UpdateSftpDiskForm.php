<?php

namespace MetaFox\Storage\Http\Resources\v1\Admin;

use MetaFox\Form\AbstractForm;
use MetaFox\Form\Builder;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateSftpDiskRequest as Request;
use MetaFox\Storage\Support\SelectDiskVisibility;
use MetaFox\Storage\Support\StorageDiskValidator;
use MetaFox\Yup\Yup;

/**
 * --------------------------------------------------------------------------
 * Form Configuration
 * --------------------------------------------------------------------------
 * stub: /packages/resources/edit_form.stub.
 */

/**
 * Class UpdateSftpForm.
 * @ignore
 * @codeCoverageIgnore
 */
class UpdateSftpDiskForm extends AbstractForm
{
    protected function prepare(): void
    {
        $resource = $this->resource;
        $action   = apiUrl('admin.storage.option.update', ['driver' => $resource['driver'], 'disk' => $resource['id']]);
        $value    = $resource['value'] ?? [];
        $value    = array_merge([
            'visibility'      => 'public',
            'driver'          => 'sftp',
            'port'            => null,
            'timeout'         => null,
            'password'        => null,
            'privateKey'      => null,
            'passphrase'      => null,
            'hostFingerprint' => null,
            'root'            => null,
            'maxTries'        => null,
            'useAgent'        => false,
            'throw'           => false,
        ], $value);

        $this->title(__p('storage::phrase.update_sfpt_title'))
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
                    ->label(__p('storage::phrase.sftp_host_name'))
                    ->yup(Yup::string()->required()),
                Builder::text('port')
                    ->label(__p('storage::phrase.sftp_port'))
                    ->description(__p('storage::phrase.sftp_port_desc'))
                    ->yup(Yup::number()->unint()->nullable()),
                Builder::text('timeout')
                    ->label(__p('storage::phrase.sftp_timeout'))
                    ->description(__p('storage::phrase.sftp_timeout_desc'))
                    ->yup(Yup::number()->unint()->min(5)->nullable()),
                Builder::text('username')
                    ->required()
                    ->label(__p('storage::phrase.sftp_username'))
                    ->yup(Yup::string()->required()),
                Builder::text('password')
                    ->label(__p('storage::phrase.sftp_password'))
                    ->description(__p('storage::phrase.password_desc'))
                    ->yup(Yup::string()->nullable()),
                // Optional SFTP Settings...
                Builder::textArea('privateKey')
                    ->label(__p('storage::phrase.sftp_private_key'))
                    ->description(__p('storage::phrase.private_key_desc'))
                    ->yup(Yup::string()->nullable()),
                Builder::text('passphrase')
                    ->label(__p('storage::phrase.sftp_passphrase'))
                    ->description(__p('storage::phrase.passphrase_desc'))
                    ->yup(Yup::string()->nullable()),
                Builder::text('hostFingerprint')
                    ->label(__p('storage::phrase.sftp_finger_print'))
                    ->description(__p('storage::phrase.fingerprint_desc'))
                    ->yup(Yup::string()->nullable()),
                Builder::text('root')
                    ->label(__p('storage::phrase.sftp_root'))
                    ->yup(Yup::string()->optional()->nullable()),
                Builder::text('url')
                    ->required()
                    ->label(__p('storage::phrase.base_url'))
                    ->yup(Yup::string()->required()),
                new SelectDiskVisibility(),
                Builder::text('maxTries')
                    ->label(__p('storage::phrase.sftp_max_retries'))
                    ->yup(Yup::number()->unint()->nullable()),
                Builder::checkbox('useAgent')
                    ->label(__p('storage::phrase.sftp_user_agent'))
                    ->description(__p('storage::phrase.sftp_use_agent_desc')),
                Builder::checkbox('throw')
                    ->required()
                    ->label(__p('storage::phrase.storage_throws')),
                Builder::hidden('driver'),
            );

        $this->addDefaultFooter(true);
    }

    /**
     * @param  Request $request
     * @return array
     */
    public function validated(Request $request, string $disk): array
    {
        $data = $request->validated();

        $data = array_merge($data, [
            'selectable'           => true,
            'label'                => sprintf('sftp%s', $disk ? ':' . $disk : ''),
            'directory_visibility' => 'public',
        ]);

        StorageDiskValidator::isValid($data);

        return $data;
    }
}
