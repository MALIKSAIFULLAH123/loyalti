<?php

namespace MetaFox\Report\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MetaFox\Localize\Support\Traits\HasTranslatableAttributes;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Traits\Eloquent\Model\HasEntity;
use MetaFox\Report\Database\Factories\ReportReasonFactory;

/**
 * Class ReportReason.
 *
 * @mixin Builder
 * @property        int    $id
 * @property        string $name
 * @property        int    $ordering
 * @property        bool   $is_default
 * @property        string $created_at
 * @property        string $updated_at
 * @method   static ReportReasonFactory factory(...$parameters)
 */
class ReportReason extends Model implements Entity
{
    use HasEntity;
    use HasFactory;
    use HasTranslatableAttributes;

    public const ENTITY_TYPE = 'report_reason';

    protected $table = 'report_reasons';

    /** @var string[] */
    protected $fillable = [
        'name',
        'is_default',
        'ordering',
    ];

    protected $translatableAttributes = [
        'name',
    ];

    /**
     * @return ReportReasonFactory
     */
    protected static function newFactory(): ReportReasonFactory
    {
        return ReportReasonFactory::new();
    }

    public function getNameAttribute(?string $value): string
    {
        return __p($value);
    }
}
