<?php

namespace MetaFox\Storage\Database\Importers;

use MetaFox\Core\Models\SiteSetting as Model;
use MetaFox\Platform\Support\JsonImporter;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateFtpDiskRequest;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateS3DiskRequest;
use MetaFox\Storage\Http\Requests\v1\Disk\Admin\UpdateSftpDiskRequest;

/*
 * stub: packages/database/json-importer.stub
 */

class FileSystemImporter extends JsonImporter
{
    public function processImport()
    {
        $this->processImportEntries();
        $this->upsertBatchEntriesInChunked(Model::class, ['id']);
    }

    protected function processImportEntry(array &$entry): void
    {
        $serviceName = $this->bundle?->source . '_' . $entry['service_id'] . '_' . str_replace('#', '_', $entry['$id']);
        $name        = sprintf('storage.filesystems.disks.%s', $serviceName);
        $configName  = sprintf('filesystems.disks.%s', $serviceName);
        $config      = $this->mappingFileSystemAttr($entry['service_id'], $entry['config']);
        $this->addEntryToBatch(Model::class, [
            'id'            => $entry['$oid'],
            'module_id'     => 'storage',
            'package_id'    => 'metafox/storage',
            'value_actual'  => json_encode($config),
            'value_default' => json_encode($config),
            'type'          => 'array',
            'config_name'   => $configName,
            'name'          => $name,
            'is_public'     => 0,
        ]);
    }

    private function mappingFileSystemAttr($serviceId, $config)
    {
        if (!is_array($config)) {
            $config = json_decode($config, true);
        }
        $rules = match ($serviceId) {
            's3', 'dospace', 's3compatible' => (new UpdateS3DiskRequest())->rules(),
            'ftp'   => (new UpdateFtpDiskRequest())->rules(),
            'sftp'  => (new UpdateSftpDiskRequest())->rules(),
            default => [],
        };
        $result = [];
        foreach ($rules as $key => $rule) {
            switch ($key) {
                case 'url':
                    $value = null;
                    if ($serviceId == 'dospace') {
                        $value = !empty($config['cdn_base_url']) ? $config['cdn_base_url'] :
                            sprintf('https://%s.%s.digitaloceanspaces.com', $config['bucket'] ?? '', $config['region'] ?? '');
                    } elseif (!empty($config['cloudfront_url'])) {
                        $value = $config['cloudfront_url'];
                    } elseif (!empty($config['base_url'])) {
                        $value = $config['base_url'];
                    }
                    $result[$key] = $value;
                    break;
                case 'root':
                    if (isset($config['base_path'])) {
                        $result[$key] = $config['base_path'];
                    } else {
                        $result[$key] = '';
                    }
                    break;
                case 'use_path_style_endpoint':
                    if (isset($config['cloudfront_enabled'])) {
                        $result[$key] = (bool) $config['cloudfront_enabled'];
                    }
                    break;
                case 'timeout':
                    if (isset($config['timeout'])) {
                        $result[$key] = (int) $config['timeout'];
                    } else {
                        $result[$key] = 60; // Default timeout
                    }
                    break;
                case 'endpoint':
                    if (!empty($config['endpoint'])) {
                        $result[$key] = $config['endpoint'];
                    } elseif ($serviceId == 'dospace') {
                        $result[$key] = sprintf('https://%s.digitaloceanspaces.com', $config['region'] ?? '');
                    }
                    break;
                default:
                    if (isset($config[$key])) {
                        $result[$key] = $config[$key];
                    } else {
                        $explodeRule = explode('|', $rule);
                        $type        = $explodeRule[1] ?? '';
                        $nullable    = ($explodeRule[2] ?? '') == 'nullable';
                        match ($type) {
                            'string'  => $result[$key] = $nullable ? null : '',
                            'int'     => $result[$key] = $nullable ? null : 0,
                            'boolean' => $result[$key] = $nullable ? null : false,
                            default   => $result[$key] = null
                        };
                    }
                    break;
            }
        }

        $result['driver'] = in_array($serviceId, ['s3', 'dospace', 's3compatible']) ? 's3' : 'local';

        return $result;
    }
}
