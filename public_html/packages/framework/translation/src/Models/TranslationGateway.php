<?php

namespace MetaFox\Translation\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Translation\Contracts\TranslationGatewayInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class TranslationGateway.
 *
 * @property int    $id
 * @property string $service
 * @property int    $is_active
 * @property string $title
 * @property string $description
 * @property array  $config
 * @property string $service_class
 * @property string $module_id
 */
class TranslationGateway extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'translation_gateway';
    public const IS_ACTIVE = 1;
    public $timestamps = false;
    protected $table = 'translation_gateway';
    protected $fillable = [
        'service',
        'is_active',
        'title',
        'description',
        'config',
        'service_class',
        'module_id',
    ];
    /** @var array<string, string> */
    protected $casts = [
        'config' => 'array',
    ];

    public function getService(): TranslationGatewayInterface
    {
        /** @var ?TranslationGatewayInterface $service */
        $service = resolve($this->service_class, ['gateway' => $this]);

        if (!$service instanceof TranslationGatewayInterface) {
            throw new ServiceNotFoundException($this->service);
        }

        return $service;
    }
}
