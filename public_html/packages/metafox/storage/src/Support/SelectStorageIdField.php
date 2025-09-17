<?php

namespace MetaFox\Storage\Support;

use Illuminate\Support\Arr;
use MetaFox\Form\Html\Choice;

/**
 * @driverName selectStorageId
 * @driverType form-field
 */
class SelectStorageIdField extends Choice
{
    private array $notIds = [];

    private array $notDrivers = [];

    public function excludes(array $ids): static
    {
        $this->notIds = $ids;

        return $this;
    }

    public function excludeDrivers(array $drivers): static
    {
        $this->notDrivers = $drivers;

        return $this;
    }

    public function prepare(): void
    {
        $disks = config('filesystems.disks', []);

        $options = [];

        foreach ($disks as $id => $value) {
            $driver = $value['driver'] ?? null;

            if (!Arr::get($value, 'selectable')) {
                continue;
            }

            if (!$driver) {
                continue;
            }

            $localDiskIds = ['local', 'public', 'web'];

            if (!in_array($id, $localDiskIds)) {
                try {
                    $isValid = StorageDiskValidator::isValid($value);

                    if (!$isValid) {
                        continue;
                    }
                } catch (\Throwable) {
                    // Just silent any exceptions being thrown
                    continue;
                }
            }

            if (in_array($id, $this->notIds)) {
                continue;
            }

            if (in_array($driver, $this->notDrivers)) {
                continue;
            }

            $options[] = ['value' => $id, 'label' => $value['label'] ?? $id];
        }

        $this->options($options);
    }
}
