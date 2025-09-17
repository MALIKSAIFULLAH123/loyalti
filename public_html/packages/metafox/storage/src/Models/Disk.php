<?php

namespace MetaFox\Storage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Facades\Settings;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Storage\Database\Factories\DiskFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class Disk.
 *
 * @property        int         $id
 * @property        string      $name
 * @property        string      $label
 * @property        string      $target
 * @property        string      $title
 * @property        bool        $is_system
 * @method   static DiskFactory factory(...$parameters)
 */
class Disk extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'storage_disks';

    protected $table = 'storage_disks';

    /** @var string[] */
    protected $fillable = [
        'id',
        'name',
        'label',
        'target',
        'is_system',
    ];

    protected static function booted()
    {
        static::deleted(function (self $model) {
            Settings::destroy('storage', ['storage.disks.' . $model->name]);
        });

        static::saved(function (self $model) {
            $name  = $model->name;
            $value = [
                'driver' => 'alias',
                'target' => $model->target,
            ];
            $configName = 'filesystems.disks.' . $name;
            Settings::createSetting(
                'storage',
                'storage.disks.' . $name,
                $configName,
                null,
                $value,
                'array',
                false,
                true
            );

            config()->set([
                $configName => $value,
            ]);
        });
    }

    /**
     * @return DiskFactory
     */
    protected static function newFactory()
    {
        return DiskFactory::new();
    }

    public function getTitleAttribute()
    {
        $config = config('filesystems.disks.' . $this->target);

        return $config['label'] ?? '';
    }
}

// end
