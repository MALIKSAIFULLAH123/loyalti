<?php

namespace MetaFox\EMoney\Models;

use Illuminate\Support\Arr;
use MetaFox\Platform\MetaFoxConstant;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\EMoney\Database\Factories\WithdrawMethodFactory;

/**
 * stub: /packages/models/model.stub.
 */

/**
 * Class WithdrawMethod.
 *
 * @property int    $id
 * @property string $title
 * @property string $description
 * @property string $service
 * @property string $service_class
 * @property string $module_id
 * @property bool   $is_active
 */
class WithdrawMethod extends Model implements Entity
{
    use HasEntity;
    use HasFactory;

    public const ENTITY_TYPE = 'ewallet_withdraw_method';

    protected $table = 'emoney_withdraw_methods';

    public $timestamps = false;

    /** @var string[] */
    protected $fillable = [
        'title',
        'description',
        'service',
        'service_class',
        'module_id',
        'is_active',
    ];

    public $casts = [
        'is_active' => 'boolean',
    ];

    public function getTitleAttribute(): string
    {
        $title = Arr::get($this->attributes, 'title');

        if (!is_string($title)) {
            return MetaFoxConstant::EMPTY_STRING;
        }

        return __p($title);
    }
}

// end
